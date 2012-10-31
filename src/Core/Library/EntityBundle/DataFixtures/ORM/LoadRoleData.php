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
namespace Core\Library\EntityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Core\Library\EntityBundle\Entity\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadRoleData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * Load all roles into the system and create the relation with the right permissions
     *
     * @param $manager \Doctrine\Common\Persistence\ObjectManager
     */
    public function load($manager)
    {
        $roleName = 'ROLE_USER_SUPERADMIN';
        $roleAlias = 'Super administrator';
        $role = new Role($roleName);
        $role->setAlias($roleAlias);
        /*
         * Package context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_DOWNLOAD_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_VIEW_ALL_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_OWNER_IN_CONTEXT_PACKAGE'));
        /*
        * User context permissions
        */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_EXPORT_SESSION_DATA_IN_CONTEXT_USER'));

        /*
         * Track data context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_TRACK_DATA'));
        /*
         * Access control context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_ACCESS_CONTROL'));
        $role->addPermission($this->getReference('PERMISSION_ASSIGN_IN_CONTEXT_ACCESS_CONTROL'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_ACCESS_CONTROL'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_ACCESS_CONTROL'));
        /**
         *  Course category context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_RETRIEVE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_COURSE_CATEGORY'));
        /*
        * System context permissions
        */
        $role->addPermission($this->getReference('PERMISSION_ACCESS_WEB_IN_CONTEXT_SYSTEM'));

        $manager->persist($role);
        $manager->flush();
        $this->addReference($roleName, $role);

        $roleName = 'ROLE_USER_ADMIN';
        $roleAlias = 'Administrator';
        $role = new Role($roleName);
        $role->setAlias($roleAlias);
        /*
         * Package context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_DOWNLOAD_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_VIEW_ALL_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_OWNER_IN_CONTEXT_PACKAGE'));
        /*
        * User context permissions
        */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_USER'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_USER'));
        /*
         * Track data context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_TRACK_DATA'));
        /*
        * System context permissions
        */
        $role->addPermission($this->getReference('PERMISSION_ACCESS_WEB_IN_CONTEXT_SYSTEM'));
        /**
         *  Course category context permissions
         */
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_RETRIEVE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_COURSE_CATEGORY'));

        $manager->persist($role);
        $manager->flush();
        $this->addReference($roleName, $role);

        $roleName = 'ROLE_USER_MOBILE';
        $roleAlias = 'Mobile user';
        $role = new Role($roleName);
        $role->setAlias($roleAlias);
        $role->addPermission($this->getReference('PERMISSION_ACCESS_MOBILE_IN_CONTEXT_SYSTEM'));
        $manager->persist($role);
        $manager->flush();
        $this->addReference($roleName, $role);

        $roleName = 'ROLE_USER_CONTENT_MANAGER';
        $roleAlias = 'Content manager';
        $role = new Role($roleName);
        $role->setAlias($roleAlias);
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_PACKAGE'));
        $role->addPermission($this->getReference('PERMISSION_VIEW_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_RETRIEVE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_CREATE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_EDIT_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_DELETE_IN_CONTEXT_COURSE_CATEGORY'));
        $role->addPermission($this->getReference('PERMISSION_ACCESS_WEB_IN_CONTEXT_SYSTEM'));
        $manager->persist($role);
        $manager->flush();
        $this->addReference($roleName, $role);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 4;
    }
}
