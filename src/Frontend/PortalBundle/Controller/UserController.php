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
use Core\Library\EntityBundle\Form\Type\UserType;

use Core\Security\AccessControlBundle\Annotations\Permissions;

class UserController extends Controller
{
    /**
     * Web UI users index
     *
     * @Route("/")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="user")
     */
    public function indexAction()
    {
        return array('entities' => $this->get('core.entity_library')->get('User')->findAllBackendUsers());
    }

    /**
     * Show the details of a user
     *
     * @Route("/show/{id}")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="user")
     */
    public function showAction($id)
    {
        return array('entity' => $this->get('service_user.users')->find($id));
    }

    /**
     * New user
     *
     * @Route("/new")
     * @Template()
     * @Method("GET")
     * @Permissions("create", context="user")
     */
    public function newAction()
    {
        $translatorService = $this->get('translator');

        $entity = new User();
        $form = $this->createForm(new UserType($translatorService), $entity);

        return array('entity' => $entity,
                     'form'   => $form->createView());
    }

    /**
     * Create a user
     *
     * @Route("/create")
     * @Template("FrontendPortalBundle:User:new.html.twig")
     * @Method("POST")
     * @Permissions("create", context="user")
     */
    public function createAction()
    {
        $translatorService = $this->get('translator');

        $entity = new User();
        $request = $this->getRequest();
        $form = $this->createForm(new UserType($translatorService), $entity, array('validation_groups' => array('Default', 'create', 'webUser')));
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($entity);
            $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setPassword($password);
            $entity->setHashId(hash('sha512', $entity->getUsername() . '{' . $entity->getSalt() . '}'));
            $em->persist($entity);
            $em->flush();

            $this->get('session')->setFlash('success', $translatorService->trans('message.create.success', array(), 'user'));

            return $this->redirect($this->generateUrl('frontend_portal_user_show', array('id' => $entity->getId())));
        }

        $this->get('session')->setFlash('error', $translatorService->trans('message.create.fail', array(), 'user'));

        return array('entity' => $entity,
                     'form'   => $form->createView());
    }

    /**
     * Edit a user
     *
     * @Route("/edit/{id}")
     * @Template()
     * @Method("GET")
     * @Permissions("edit", context="user")
     */
    public function editAction($id)
    {
        $translatorService = $this->get('translator');

        $entity = $this->get('service_user.users')->find($id);

        $entity->setPassword(''); // Leave password blank as default
        $editForm = $this->createForm(new UserType($translatorService), $entity);

        return array('entity'    => $entity,
                     'edit_form' => $editForm->createView());
    }

    /**
     * Update a user
     *
     * @Route("/update/{id}")
     * @Template("FrontendPortalBundle:User:edit.html.twig")
     * @Method("POST")
     * @Permissions("edit", context="user")
     */
    public function updateAction($id)
    {
        $translatorService = $this->get('translator');

        $entity = $this->get('service_user.users')->find($id);

        $oldPassword = $entity->getPassword();

        $request = $this->getRequest();
        $editForm = $this->createForm(new UserType($translatorService), $entity, array('validation_groups' => array('Default', 'update', 'webUser')));
        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            // If password is empty, then you don't need to update the password, just use the old one
            $password = $entity->getPassword();
            if ($password == '') {
                $entity->setPassword($oldPassword);
            } else {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($entity);
                $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
                $entity->setPassword($password);
            }

            $entity->setHashId(hash('sha512', $entity->getUsername() . '{' . $entity->getSalt() . '}'));

            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->setFlash('success', $translatorService->trans('message.update.success', array(), 'user'));

            return $this->redirect($this->generateUrl('frontend_portal_user_show', array('id' => $id)));
        }

        $this->get('session')->setFlash('error', $translatorService->trans('message.update.fail', array(), 'user'));

        return array('entity'    => $entity,
                     'edit_form' => $editForm->createView());
    }

    /**
     * Update password
     *
     * @Route("/updatepassword/", name="user_update_password", options={"expose"=true})
     * @Template("")
     * @Method("POST")
     */
    public function updatePasswordAction()
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
        $request = $this->getRequest();
        $current = $request->get('current');
        $new = $request->get('new');
        $verification = $request->get('verification');

        try {
            $this->get('service_user.users')->updatePassword($current, $new, $verification);
            return new Response($serializer->serialize(true, 'json'));
        } catch (\Exception $e) {
            return new Response($serializer->serialize($e->getMessage(), 'json'));
        }
    }

    /**
     * Delete a user
     *
     * @Route("/delete/{id}", name="user_delete", options={"expose"=true})
     * @Template()
     * @Method("GET")
     * @Permissions("delete", context="user")
     */
    public function deleteAction($id)
    {
        $translatorService = $this->get('translator');

        $this->get('service_user.users')->delete($id);

        $this->get('session')->setFlash('success', $translatorService->trans('message.delete.success', array(), 'user'));

        return $this->redirect($this->generateUrl('frontend_portal_user_index'));
    }

    /**
     * Verify if user has packages
     *
     * @Route("/has_packages/{id}", name="user_has_packages", options={"expose"=true}, defaults={"_format"="json"})
     * @Method("GET")
     * @Permissions("delete", context="user")
     */
    public function hasPackagesAction($id)
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));

        $usersService = $this->get('service_user.users');

        if ($usersService->hasPackages($id)) {
            return new Response($serializer->serialize($usersService->getSubstitutePackageOwners($id), 'json'));
        }

        return new Response($serializer->serialize(false, 'json'));
    }

    /**
     * Update a package owner and Delete the User entity.
     *
     * @Route("/update_and_delete/{id}/{new_id}", name="user_update_and_delete", options={"expose"=true})
     * @Method("GET")
     * @Permissions("delete", context="user")
     */
    public function updateAndDeleteAction($id, $new_id)
    {
        $translatorService = $this->get('translator');

        $this->get('service_user.users')->updatePackagesAndDelete($id, $new_id);

        $this->get('session')->setFlash('success', $translatorService->trans('message.delete.success', array(), 'user'));

        return $this->redirect($this->generateUrl('frontend_portal_user_index'));
    }
}
