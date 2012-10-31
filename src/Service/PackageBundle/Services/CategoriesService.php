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

use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Core\Security\AccessControlBundle\Services\AccessControlService;
use Core\Library\EntityBundle\Entity\PermissionContext;

class CategoriesService
{
    protected $entityLibraryService;
    protected $validatorService;
    protected $securityContext;
    protected $loggerService;
    protected $em;
    protected $translatorService;
    protected $accessControl;

    public function __construct(Logger $loggerService, EntityLibrary $entityLibraryService, SecurityContext $securityContext, Validator $validatorService, Translator $translatorService, AccessControlService $accessControl)
    {
        $this->entityLibraryService = $entityLibraryService;
        $this->validatorService = $validatorService;
        $this->securityContext = $securityContext;
        $this->loggerService = $loggerService;
        $this->em = $entityLibraryService->getManager();
        $this->translatorService = $translatorService;
        $this->accessControl = $accessControl;
    }

    /**
     * Recursive function to get all categories as an array if all chidren mapped inside
     * of 'subitems' key of each returned object.
     *
     * If no parameter is provided then it automatically picks the system root categories
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $children (optional) An array of categories
     *
     * @return array An array with the following keys: id, name, subitems (which has the same structure of the parent node);
     */
    public function getCategoriesAsTree($children = null)
    {
        $tree = array();

        if (is_null($children)) {
            $categories = $this->entityLibraryService->get('CourseCategory')->findBy(array('parent' => null));
        } else {
            $categories = $children;
        }

        foreach ($categories as $category) {
            /** @var $category \Core\Library\EntityBundle\Entity\CourseCategory */
            $catEntry = array(
                'id'       => $category->getId(),
                'name'     => $this->getCategoryI18n($category->getName()),
                'subitems' => array(),
            );
            if ($category->hasChildren()) {
                $catEntry['subitems'] = $this->getCategoriesAsTree($category->getChildren());
            }
            $tree[] = $catEntry;
        }

        return $tree;
    }

    public function getCategoryI18n($name)
    {
        return array('en' => $name);
    }

    public function countCategories()
    {
        return $this->entityLibraryService->get('CourseCategory')->countCategories();
    }
}
