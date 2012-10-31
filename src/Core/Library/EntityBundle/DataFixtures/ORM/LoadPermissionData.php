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
use Core\Library\EntityBundle\Entity\PermissionContext;
use Core\Library\EntityBundle\Entity\Permission;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadPermissionData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * @var $manager \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     * Load all permissions into the system
     *
     * @param $manager \Doctrine\Common\Persistence\ObjectManager
     */
    public function load($manager)
    {
        $this->manager = $manager;

        /*
        * Package context permissions fixtures
        */
        $permissions = array('view'       => 'View',
                             'create'     => 'Create',
                             'edit'       => 'Edit',
                             'delete'     => 'Delete',
                             'download'   => 'Download',
                             'view_all'   => 'View from all Organisations',
                             'edit_owner' => 'Edit Owner');
        $this->generatePermissionsInContext($permissions, 'package');

        /*
        * User context permissions fixtures
        */
        $permissions = array('view'                => 'View',
                             'create'              => 'Create',
                             'edit'                => 'Edit',
                             'delete'              => 'Delete',
                             'export_session_data' => 'Export Session Data');
        $this->generatePermissionsInContext($permissions, 'user');

        /*
        * TrackData context permissions fixtures
        */
        $permissions = array('View' => 'View');
        $this->generatePermissionsInContext($permissions, 'track_data');

        /*
        * Access control context permissions fixtures
        */
        $permissions = array('view'   => 'View',
                             'assign' => 'Assign perms to roles',
                             'create' => 'Create role',
                             'delete' => 'Delete role');
        $this->generatePermissionsInContext($permissions, 'access_control');

        /*
        * Course Category context permissions fixtures
        */
        $permissions = array('view'     => 'View',
                             'retrieve' => 'Retrieve',
                             'create'   => 'Create',
                             'edit'     => 'Edit',
                             'delete'   => 'Delete');
        $this->generatePermissionsInContext($permissions, 'course_category');

        /*
        * System context permissions fixtures
        */
        $permissions = array('access_web'    => 'Access web suite',
                             'access_mobile' => 'Access mobile interface');
        $this->generatePermissionsInContext($permissions, 'system');
    }

    /**
     * Create a set of permissions in a pre-defined context namespace
     *
     * @param $permissions           array A map of permission => description
     * @param $permissionContextName string The permission context alias
     */
    private function generatePermissionsInContext($permissions, $permissionContextName)
    {
        $manager = $this->manager;
        foreach ($permissions as $name => $description) {
            /** @var $permissionContext \Core\Library\EntityBundle\Entity\PermissionContext */
            $permissionContext = $this->getReference('PERMISSION_CONTEXT_' . trim(strtoupper($permissionContextName)));
            $permission = new Permission($name, $permissionContext, $description);
            $manager->persist($permission);
            $this->addReference('PERMISSION_' . trim(strtoupper($name)) . '_IN_CONTEXT_' . trim(strtoupper($permissionContext->getName())), $permission);
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 3;
    }
}
