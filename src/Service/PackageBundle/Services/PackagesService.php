<?php
/*
 * Copyright (c) 2012, TATRC and Tribal
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * * Neither the name of TATRC or TRIBAL nor the
 *   names of its contributors may be used to endorse or promote products
 *   derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL TATRC OR TRIBAL BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Service\PackageBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Core\Library\EntityBundle\Entity\User;
use Core\Library\EntityBundle\Entity\Package;
use Core\Library\EntityBundle\Entity\Organisation;
use Core\Library\EntityBundle\Entity\File;
use Monolog\Logger;
use Service\ImageBundle\Services\ImagesService;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Core\Security\AccessControlBundle\Services\AccessControlService;
use Core\Library\EntityBundle\Entity\PermissionContext;

class PackagesService
{
    protected $entityLibraryService;
    protected $validatorService;
    protected $securityContext;
    protected $loggerService;
    protected $em;
    protected $imageService;
    protected $translatorService;
    protected $accessControl;

    public function __construct(Logger $loggerService, EntityLibrary $entityLibraryService, SecurityContext $securityContext, Validator $validatorService, ImagesService $imageService, Translator $translatorService, AccessControlService $accessControl)
    {
        $this->entityLibraryService = $entityLibraryService;
        $this->validatorService = $validatorService;
        $this->securityContext = $securityContext;
        $this->loggerService = $loggerService;
        $this->em = $entityLibraryService->getManager();
        $this->imageService = $imageService;
        $this->translatorService = $translatorService;
        $this->accessControl = $accessControl;
    }

    /**
     * Find a package by id
     *
     * Logged user needs access permissions to that package
     *
     * @param $id The package id
     * @return Package The package
     * @throws \Exception If the package is not found or the logged user doesn' have access to it
     */
    public function find($id)
    {
        $package = $this->entityLibraryService->get('Package')->find($id);
        $user = $this->securityContext->getToken()->getUser();

        if (is_null($package))
            throw new \Exception($this->translatorService->trans('message.find.fail', array(), 'package'));

        if (!$this->userCanAccessPackage($package, $user))
            throw new \Exception($this->translatorService->trans('message.access.denied', array(), 'package'));

        return $package;
    }

    /**
     * Find all packages for the current logged user
     * If the logged user has permission to view all packages, all of them will be returned.
     * Otherwise, only the packages that have the owner in the same organisation of the curren user will be returned.
     *
     * @return array An array of Package objects
     */
    public function findPackagesForCurrentUser()
    {
        $packageRepository = $this->entityLibraryService->get('Package');

        return $this->accessControl->hasPermissions('view_all', 'package')
            ? $packageRepository->findAll()
            : $packageRepository->getPackagesByOrganisation($this->securityContext->getToken()->getUser()->getOrganisation());
    }

    /**
     * Save a package
     *
     * @param Package $package
     */
    public function save($package)
    {
        if ($package->getId() == null) $package->setUser($this->securityContext->getToken()->getUser());

        $package->setUpdatedAt(new \DateTime());

//        if ($package->getThumbnailContent() != null) $package->setThumbnail(new File($package->getThumbnailContent()));

//        foreach ($package->getFiles() as $file) {
//            if ($file->getFileContent() != null) $file->setFile(new File($file->getFileContent(), false));
//            $file->setPackage($package);
//        }

        $this->em->persist($package);
        $this->em->flush();
        
        //TODO Find another way to flush the Package->postPersist action.
        $this->em->flush();

        // Resize thumbnail after save if necessary
        if ($package->getThumbnailContent() != null) $this->imageService->resize($package->getThumbnail()->getAbsolutePath());
    }

    /**
     * Get all published packages as array data
     *
     * If you provide a request parameter as the entry point, then the portal host and services hosts
     * will be automatically generated, otherwise it defaults to:
     * - Portal: http://app-services.localserver.dev
     * - Services: http://app.localserver.dev
     *
     * @param null|\Symfony\Component\HttpFoundation\Request $request
     * @return array An array map with all relevant package information: uniqueId, title, description, categories, username, organisation, thumbnailUrl, metadata
     */
    public function getPublishedPackages(Request $request = null)
    {
        $packages = $this->entityLibraryService->get('Package')->getPublishedPackages();
        $publishedPackages = array();

        // TODO: read from parameters
        $portalFullHost = 'http://app.localserver.dev/';
        $serviceFullHost = 'http://app-services.localserver.dev/';

        if (!is_null($request)) {
            $protocol = ($request->isSecure()) ? 'https://' : 'http://';
            $host = $request->getHost();
            $serviceFullHost = $protocol . $host . '/';
            $hostPortal = str_replace('-services', '', $host);
            $portalFullHost = $protocol . $hostPortal . '/';
        }

        foreach ($packages as $package) {
            /** @var $package Package */
            $thumbnailPath = $package->getThumbnail() == null ? 'images/default_thumbnail.png' : $package->getThumbnail()->getWebPath();

            // TODO: use serializer and normalizers
            $metadataArray = array();
            $metadataArrayCollection = $package->getMetadata();
            foreach($metadataArrayCollection as $metadata) {
                $metadataArray[$metadata->getName()] = $metadata->getValue();
            }

            if (empty($metadataArray)) {
                $metadataArray = new \stdClass();
            }

            // TODO: remove courseCode
            $currentPackage = array(
                'uniqueId'     => $package->getUniqueId(),
                'title'        => $package->getTitle(),
                'description'  => $package->getDescription(),
                'categories'   => $package->getCategoriesArray(),
                'username'     => $package->getUser()->getUsername(),
                'organisation' => $package->getUser()->getOrganisation()->getName(),
                'thumbnailUrl' => $portalFullHost . $thumbnailPath,
                'metadata'     => $metadataArray,
            );

            $packageFiles = $package->getFiles();

            /* @var $packageFile \Core\Library\EntityBundle\Entity\PackageFile */
            foreach ($packageFiles as $packageFile) {
                /* @var $packageFile \Core\Library\EntityBundle\Entity\File */
                $file = $packageFile->getFile();
                if (!$file) continue;

                $currentFile = array(
                    'version' => $packageFile->getVersion(),
                    'md5sum'  => $file->getMD5sum(),
                    'size'    => $file->getSize(),
                    'url'     => $serviceFullHost . 'package-layer/packages/' . $package->getId() . '/files/' . $packageFile->getId(),
                );

                $fileMetadata = array();
                $metadataCollection = $packageFile->getMetadata();
                foreach ($metadataCollection as $metadata) {
                    $fileMetadata[$metadata->getName()] = $metadata->getValue();
                }

                if (empty($fileMetadata)) {
                    $fileMetadata = new \stdClass();
                }

                $currentFile['metadata'] = $fileMetadata;
                $currentPackage['files'][] = $currentFile;
            }

            $publishedPackages[] = $currentPackage;
        }

        return $publishedPackages;
    }

    /**
     * Verify if a user can access a package
     * It must belongs to the same organization of the package owner or at least
     * have the view_all packages permission.
     *
     * @param \Core\Library\EntityBundle\Entity\Package $package The package to check the access against
     * @param \Core\Library\EntityBundle\Entity\User $user The user that is trying to access
     * @return bool
     */
    public function userCanAccessPackage(Package $package, User $user)
    {
        return $this->accessControl->hasPermissions('view_all', 'package')
            || $package->getUser()->getOrganisation()->getId() == $user->getOrganisation()->getId();
    }

    /**
     * Delete a package by id
     *
     * @param $id The package id to be deleted
     */
    public function delete($id)
    {
        $package = $this->find($id);
        $this->em->remove($package);
        $this->em->flush();
    }
}
