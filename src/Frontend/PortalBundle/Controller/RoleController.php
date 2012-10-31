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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Core\Security\AccessControlBundle\Annotations\Permissions;
use Core\Library\EntityBundle\Entity\Role;

class RoleController extends Controller
{
    /**
     * Index of roles
     *
     * @Route("/")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="access_control")
     */
    public function indexAction()
    {
        $roles = $this->get('core.entity_library')->get('Role')->findAll();
        $contexts = $this->get('core.entity_library')->get('PermissionContext')->findAll();

        return array('roles'    => $roles,
                     'contexts' => $contexts);
    }

    /**
     * Get all permissions from a role id
     *
     * @Route("/getPermission/{role_id}", name="role_getPermission")
     * @Template()
     * @Method("GET")
     * @Permissions("view", context="access_control")
     */
    public function getPermissionAction($role_id)
    {
        $role = $this->get('core.entity_library')->get('Role')->getAllPermissions($role_id);

        $response = new Response(json_encode($role));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Set or unset a permission
     *
     * @Route("/setPermission", name="role_setPermission")
     * @Template()
     * @Method("POST")
     * @Permissions("assign", context="access_control")
     */
    public function setPermissionAction()
    {
        $request = $this->get('request');

        $roleId = $request->get('role');
        $permissionId = $request->get('permission');
        $insert = $request->get('insert');

        /** @var $role \Core\Library\EntityBundle\Entity\Role */
        $role = $this->get('core.entity_library')->get('Role')->findOneById($roleId);
        $permission = $this->get('core.entity_library')->get('Permission')->findOneById($permissionId);

        if (($role) && ($permission)) {
            if ($insert == "true")
                $role->addPermission($permission);
            else
                $role->delPermission($permission);

            /** @var $em \Doctrine\ORM\EntityManager */
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($role);
            $em->flush();
        }

        $response = new Response(json_encode(array($this->get('core.entity_library')->get('Role')->findOneById($roleId)->getPermissionsArray())));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Create a role
     *
     * @Route("/create", name="role_create", options={"expose"=true}, defaults={"_format"="json"})
     * @Template()
     * @Method("POST")
     * @Permissions("create", context="access_control")
     */
    public function createAction()
    {
        $return = array('status' => 'success');
        $request = $this->get('request');

        $role = new Role();
        $role->setName($request->get('name'));
        $alias = (trim($request->get('alias')) == '') ? $role->getName() : $request->get('alias');
        $role->setAlias($alias);

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($role);
        $em->flush();

        $return['id'] = $role->getId();

        $response = new Response(json_encode($return));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Delete a role
     *
     * @Route("/delete/{roleId}", name="role_delete", options={"expose"=true}, defaults={"_format"="json"})
     * @Template()
     * @Method("POST")
     * @Permissions("delete", context="access_control")
     */
    public function deleteAction($roleId)
    {
        $return = array('status' => 'success');

        $this->get('core.entity_library')->get('Role')->delete($roleId);

        $response = new Response(json_encode($return));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
