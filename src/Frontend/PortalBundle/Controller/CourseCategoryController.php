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
namespace Frontend\PortalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Core\Library\EntityBundle\Entity\File;
use Symfony\Component\HttpFoundation\Response;
use Core\Library\EntityBundle\Entity\CourseCategory;
use Core\Security\AccessControlBundle\Annotations\Permissions;

class CourseCategoryController extends Controller {

    /**
     * @Route("/")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="course_category")
     */
    public function indexAction() {
        return array();
    }

    /**
     * Retrieve a JSON with the categories tree
     *
     * @Route("/retrieve", name="course_category_retrieve", options={"expose"=true}, defaults={"_format"="json"})
     * @Method("GET")
     * @Permissions("retrieve", context="course_category")
     */
    public function retrieveAction() {
        $categories = $this->get('core.entity_library')->get('CourseCategory')->findBy(array("parent" => null));
        $categoriesArray = array("data" => "Categories", "children" => $this->extractCategories($categories), "attr" => array("id" => -1));
        return new Response(json_encode($categoriesArray), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Recursive function to extract all categories in a tree style
     *
     * @param $categories
     * @return array
     */
    private function extractCategories($categories) {
        if (!is_array($categories)) $categories = $categories->toArray();
        
        $categoriesArray = array();
        usort($categories, array("Core\Library\EntityBundle\Entity\CourseCategory", "compare"));
        foreach ($categories as $category) {
            $categoriesArray[] = array("data" => $category->getName(), "children" => $this->extractCategories($category->getChildren()), "attr" => array("id" => $category->getId()));
        }
        return $categoriesArray;
    }

    /**
     * Create a category
     *
     * @Route("/create")
     * @Method("POST")
     * @Permissions("create", context="course_category")
     */
    public function createAction() {
        $translatorService = $this->get('translator');
        $courseRepository = $this->get('core.entity_library')->get('CourseCategory');
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->getRequest();
        $name = $request->request->get('name');
        $parent_id = $request->request->get('parent_id');
        $parent = $parent_id ? $courseRepository->find($parent_id) : null;

        if ($parent != null && count($parent->getPackages()) > 0) {
            $response = new Response();
            $response->setContent(json_encode(array("status" => false, "message" => $translatorService->trans('message.create.error.has_packages', array(), 'course_category'))));
            $response->headers->set('Content-Type', 'application/json');
            return $response;              
        }
        
        $category = new CourseCategory;
        $category->setName($name);
        $category->setParent($parent);
        $em->persist($category);
        $em->flush();

        return new Response(json_encode(array("status" => true, "id" => $category->getId())), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Update a category
     *
     * @Route("/update/{id}")
     * @Method("POST")
     * @Permissions("edit", context="course_category")
     */
    public function updateAction($id) {
        $courseRepository = $this->get('core.entity_library')->get('CourseCategory');
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->getRequest();
        $name = $request->request->get('name');

        $category = $courseRepository->find($id);
        $category->setName($name);
        $em->persist($category);
        $em->flush();

        return new Response(json_encode(array("status" => true)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * Delete a category
     *
     * @Route("/delete/{id}")
     * @Method("GET")
     * @Permissions("delete", context="course_category")
     */
    public function deleteAction($id) {
        $courseRepository = $this->get('core.entity_library')->get('CourseCategory');
        $em = $this->getDoctrine()->getEntityManager();

        $category = $courseRepository->find($id);
        $em->remove($category);
        $em->flush();

        return new Response(json_encode(array("status" => true)), 200, array('Content-Type' => 'application/json'));
    }

}
