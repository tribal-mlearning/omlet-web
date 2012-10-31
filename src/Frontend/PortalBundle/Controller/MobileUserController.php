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

use Core\Library\EntityBundle\Services\EntityLibrary;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

use Core\Library\EntityBundle\Entity\User;
use Core\Library\EntityBundle\Form\Type\MobileUserType;

use Core\Security\AccessControlBundle\Annotations\Permissions;

class MobileUserController extends Controller
{
    /**
     * All mobile users index
     *
     * @Route("/")
     * @Template("FrontendPortalBundle:MobileUser:index.html.twig")
     * @Method("GET")
     * @Permissions("view", context="user")
     */
    public function indexAction()
    {
        return array(
            'entities' => $this->get('core.entity_library')->get('User')->findAllMobileUsers()
        );
    }

    /**
     * New mobile user
     *
     * @Route("/new")
     * @Template("FrontendPortalBundle:MobileUser:new.html.twig")
     * @Method("GET")
     * @Permissions("create", context="user")
     */
    public function newAction()
    {
        $translatorService = $this->get('translator');

        $entity = new User();
        $form = $this->createForm(new MobileUserType($translatorService), $entity);

        return array('entity' => $entity,
                     'form'   => $form->createView());
    }

    /**
     * Show a mobile user
     *
     * @Route("/show/{id}")
     * @Template("FrontendPortalBundle:MobileUser:show.html.twig")
     * @Method("GET")
     * @Permissions("view", context="user")
     */
    public function showAction($id)
    {
        $translatorService = $this->get('translator');

        $entity = $this->get('service_user.users')->find($id);

        $form = $this->createForm(new MobileUserType($translatorService), $entity);
        return array('entity' => $entity,
                     'form'   => $form->createView());
    }

    /**
     * Create a mobile user
     *
     * @Route("/create")
     * @Template("FrontendPortalBundle:MobileUser:new.html.twig")
     * @Method("POST")
     * @Permissions("create", context="user")
     */
    public function createAction()
    {
        $translatorService = $this->get('translator');

        $entity = new User();
        $request = $this->getRequest();
        $form = $this->createForm(new MobileUserType($translatorService), $entity, array('validation_groups' => array('Default', 'createMobileUser')));
        $form->bindRequest($request);

        if ($form->isValid()) {
            $userService = $this->get('service_user.users');

            $userService->persistMobileUser($entity);

            $this->get('session')->setFlash('success', $translatorService->trans('message.create.success', array(), 'user'));

            return $this->redirect($this->generateUrl('frontend_portal_mobileuser_show', array('id' => $entity->getId())));
        }

        $this->get('session')->setFlash('error', $translatorService->trans('message.create.fail', array(), 'user'));

        return array('entity' => $entity,
                     'form'   => $form->createView());
    }

    /**
     * Edit a mobile user
     *
     * @Route("/edit/{id}")
     * @Template("FrontendPortalBundle:MobileUser:edit.html.twig")
     * @Method("GET")
     * @Permissions("edit", context="user")
     */
    public function editAction($id)
    {
        $translatorService = $this->get('translator');

        $entity = $this->get('service_user.users')->find($id);

        $editForm = $this->createForm(new MobileUserType($translatorService), $entity);

        return array('entity'    => $entity,
                     'edit_form' => $editForm->createView());
    }

    /**
     * Update a mobile user
     *
     * @Route("/update/{id}")
     * @Template("FrontendPortalBundle:MobileUser:edit.html.twig")
     * @Method("POST")
     * @Permissions("edit", context="user")
     */
    public function updateAction($id)
    {
        $translatorService = $this->get('translator');

        $entity = $this->get('service_user.users')->find($id);

        $request = $this->getRequest();
        $editForm = $this->createForm(new MobileUserType($translatorService), $entity, array('validation_groups' => array('Default', 'updateMobileUser')));
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $entity->setHashId(hash('sha512', $entity->getUsername() . '{' . $entity->getSalt() . '}'));

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->setFlash('success', $translatorService->trans('message.update.success', array(), 'user'));

            return $this->redirect($this->generateUrl('frontend_portal_mobileuser_show', array('id' => $id)));
        }

        $this->get('session')->setFlash('error', $translatorService->trans('message.update.fail', array(), 'user'));

        return array('entity'    => $entity,
                     'edit_form' => $editForm->createView());
    }

    /**
     * Delete a mobile user
     *
     * @Route("/delete/{id}", name="mobile_user_delete", options={"expose"=true}, defaults={"_format"="json"})
     * @Template()
     * @Method("GET")
     * @Permissions("delete", context="user")
     */
    public function deleteAction($id)
    {
        $translatorService = $this->get('translator');

        $this->get('service_user.users')->delete($id);

        $this->get('session')->setFlash('success', $translatorService->trans('message.delete.success', array(), 'user'));

        return $this->redirect($this->generateUrl('frontend_portal_mobileuser_index'));
    }

}