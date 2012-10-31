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
namespace Service\PackageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class PackagesController extends Controller
{
    /**
     * REST GET /packages
     * Find all published packages and categories if they exists, otherwise it will return just packages.
     *
     * JSON response example:
     *{
     *   "categories":[
     *      {
     *         "id":1,
     *         "name":{
     *            "en":"People"
     *         },
     *         "subitems":[
     *            {
     *               "id":2,
     *               "name":{
     *                  "en":"Male"
     *               },
     *               "subitems":[
     *                  {
     *                     "id":3,
     *                     "name":{
     *                        "en":"Soccer"
     *                     },
     *                     "subitems":[
     *
     *                     ]
     *                  }
     *               ]
     *            }
     *         ]
     *      },
     *      {
     *         "id":4,
     *         "name":{
     *            "en":"Vehicles"
     *         },
     *         "subitems":[
     *            {
     *               "id":5,
     *               "name":{
     *                  "en":"Cars"
     *               },
     *               "subitems":[
     *                  {
     *                     "id":6,
     *                     "name":{
     *                        "en":"Driving lessons"
     *                     },
     *                     "subitems":[
     *
     *                     ]
     *                  }
     *               ]
     *            }
     *         ]
     *      }
     *   ],
     *   "packages":[
     *      {
     *         "categories":[
     *            3
     *         ],
     *         "description":"By the end of this course, you will know all soccer rules.",
     *         "files":[
     *            {
     *               "md5sum":"f12990e19d27760bcdc226b386528ea9",
     *               "metadata":{
     *
     *               },
     *               "size":"675.6",
     *               "url":"http://app-services.localserver.dev/package-layer/packages/1/files/1",
     *               "version":"1"
     *            }
     *         ],
     *         "metadata":{
     *            "duration":"60",
     *            "language":"en",
     *            "level":"1"
     *         },
     *         "organisation":"OrganisationName",
     *         "thumbnailUrl":"http://app.localserver.dev/images/default_thumbnail.png",
     *         "title":"Soccer rules",
     *         "uniqueId":"365011b5464c427",
     *         "username":"system-uploader"
     *      },
     *      {
     *         "categories":[
     *            6
     *         ],
     *         "description":"By the end of this course, you will know how to drive a car",
     *         "files":[
     *            {
     *               "md5sum":"b08bd9916b917766283a754ecb0ae3c3",
     *               "metadata":{
     *
     *               },
     *               "size":"807.3",
     *               "url":"http://app-services.localserver.dev/package-layer/packages/2/files/2",
     *               "version":"1"
     *            }
     *         ],
     *         "metadata":{
     *            "duration":"60",
     *            "language":"en",
     *            "level":"1"
     *         },
     *         "organisation":"OrganisationName",
     *         "thumbnailUrl":"http://app.localserver.dev/images/default_thumbnail.png",
     *         "title":"Driver guidelines",
     *         "uniqueId":"22500d5f6dbe576",
     *         "username":"system-uploader"
     *      }
     *   ]
     *}
     *
     * @return \Symfony\Component\HttpFoundation\Response A JSON response
     */
    public function getPackagesAction()
    {
        $packagesService = $this->get('service_package.packages');
        /** @var $categoriesService \Service\PackageBundle\Services\CategoriesService */
        $categoriesService = $this->get('service_package.categories');

        $packages = $packagesService->getPublishedPackages($this->get('request'));
        $allCategories = $categoriesService->getCategoriesAsTree();

        if (sizeof($allCategories) > 0) {
            $packages = array('packages'   => $packages,
                              'categories' => $allCategories);
        }

        // This json encode is needed because the serializer has a problem with json empty objects, it translates into a empty array instead of empty map
        $response = new Response(@json_encode($packages), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * REST GET /packages/{packageId}/files/{fileId}
     * Get the file specified by the fileId for a given package specified by the packageId
     *
     * @param $packageId The id of the package to download some file
     * @param $fileId The file id to be download from the specified package
     *
     * @return \Symfony\Component\HttpFoundation\Response A binary data response
     */
    public function getPackageFileAction($packageId, $fileId)
    {
        $entityLibraryService = $this->get('core.entity_library');
        $packageFile = $entityLibraryService->get('PackageFile')->find($fileId);

        $response = new Response(file_get_contents($packageFile->getFile()->getAbsolutePath()), 200);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $packageFile->getFile()->getName() . '"');

        return $response;
    }
}
