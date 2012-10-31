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
namespace Core\Security\AccessControlBundle\Services;

use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Entity\User;
use Core\Library\EntityBundle\Entity\PermissionContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Monolog\Logger;
use Core\Library\EntityBundle\Entity\Permission;

class AccessControlService
{
    protected $entityLibraryService;
    protected $securityContext;
    protected $loggerService;
    protected $em;

    public function __construct(Logger $loggerService, EntityLibrary $entityLibraryService, SecurityContext $securityContext)
    {
        $this->entityLibraryService = $entityLibraryService;
        $this->securityContext = $securityContext;
        $this->loggerService = $loggerService;
        $this->em = $entityLibraryService->getManager();
    }

    /**
     * Check if authenticated/supplied user has the specified permissions, if a permission doesn't exists on DB, it tries to create it.
     *
     * @param string|array $permissionsToCheck       A single permission or an array of permissions to check access
     * @param string       $context                  (optional) The context which permissions are being validated to, defaults to: SYSTEM
     * @param User         $user                     (optional) Overrides the permissions check against the authenticated user with the supplied user instead
     *
     * @return bool true|false True if the user has the right permission to proceed
     */
    public function hasPermissions($permissionsToCheck, $context = PermissionContext::DEFAULT_CONTEXT, User $user = null)
    {
        if (is_null($context)) {
            $context = PermissionContext::DEFAULT_CONTEXT;
        }
        $context = strtoupper($context);

        if (is_null($user)) {
            $authToken = $this->securityContext->getToken();
            if (is_null($authToken))
                return false;
            $loggedUser = $authToken->getUser();
            if (is_null($loggedUser))
                return false;
        } else {
            $loggedUser = $user;
        }
        if (!is_array($permissionsToCheck)) {
            $singlePerm = $permissionsToCheck;
            $permissionsToCheck = array($singlePerm);
        }

        $roles = $this->entityLibraryService->get('Role')->getAllRolesFromUser($loggedUser->getId());
        foreach ($permissionsToCheck as $permissionName) {
            $permissionName = strtoupper($permissionName);
            $permissionToFind = $this->entityLibraryService->get('Permission')->findOneByNameAndContext($permissionName, $context);
            if (is_null($permissionToFind)) {
                $contextToFind = $this->entityLibraryService->get('PermissionContext')->findOneBy(array('name' => $context));
                if (is_null($contextToFind)) {
                    $newContext = new PermissionContext();
                    $newContext->setName($context);
                    $contextToFind = $newContext;
                }
                $newPermission = new Permission();
                $newPermission->setName($permissionName);
                $newPermission->setContext($contextToFind);
                $this->em->persist($newPermission);
                $this->em->flush();
            }
        }

        foreach ($roles as $role) {
            /* @var $role \Core\Library\EntityBundle\Entity\Role */
            $rolePermissions = $role->getPermissions();
            foreach ($permissionsToCheck as $key => $permissionName) {
                $permissionName = strtoupper($permissionName);
                $permissionToFind = $this->entityLibraryService->get('Permission')->findOneByNameAndContext($permissionName, $context);
                if (in_array($permissionToFind, $rolePermissions->toArray())) {
                    unset($permissionsToCheck[$key]);
                }
            }
            $permissionsToCheck = array_values($permissionsToCheck);
        }

        if (sizeof($permissionsToCheck) == 0) {
            return true;
        } else {
            return false;
        }
    }
}

