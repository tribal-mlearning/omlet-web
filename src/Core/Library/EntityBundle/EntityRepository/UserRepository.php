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
namespace Core\Library\EntityBundle\EntityRepository;

use Doctrine\ORM\Query\ResultSetMapping;
use Core\Library\EntityBundle\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class UserRepository extends EntityRepository
{
    /**
     * Get a DQL to find all users
     *
     * @return \Doctrine\ORM\Query The query to find all users
     */
    public function findAllDQL()
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('Core\Library\EntityBundle\Entity\User', 'u')
            ->getQuery();
    }

    /**
     * Find all users that can be associated as package owners except by the current owner
     *
     * @param $userId Current owner user id
     * @return array An array of Users able to be the new owners
     */
    public function findSubstitutePackageOwners($userId)
    {
        $adminId = $this->getRoleId('ROLE_USER_ADMIN');
        $contentManagerId = $this->getRoleId('ROLE_USER_CONTENT_MANAGER');

        return $this->createQueryBuilder('u')
            ->select("u.id id, CONCAT(CONCAT(u.username, ' ('), CONCAT(o.name, ')')) username")
            ->join('u.organisation', 'o')
            ->where('u.id != :userId')
            ->andWhere(':adminId MEMBER OF u.userRoles OR :contentManagerId MEMBER OF u.userRoles')
            ->orderBy('o.name, u.username', 'ASC')
            ->setParameter('userId', $userId)
            ->setParameter('adminId', $adminId)
            ->setParameter('contentManagerId', $contentManagerId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the role id from a role name
     *
     * @param $roleName The role name to discover the id
     * @return int The role id
     */
    public function getRoleId($roleName)
    {
        return $this->_em->createQueryBuilder()
            ->select('r.id')
            ->from('Core\Library\EntityBundle\Entity\Role', 'r')
            ->where('r.name = :roleName')
            ->getQuery()
            ->setParameter('roleName', $roleName)
            ->getSingleScalarResult();
    }

    /**
     * Find all users that are not mobile ones or the super admin
     *
     * @return array An array of users
     */
    public function findAllBackendUsers()
    {
        $mobileId = $this->getRoleId('ROLE_USER_MOBILE');
        $superAdminId = $this->getRoleId('ROLE_USER_SUPERADMIN');

        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('Core\Library\EntityBundle\Entity\User', 'u')
            ->join('u.userRoles', 'r')
            ->where(':mobileId NOT MEMBER OF u.userRoles AND :superAdminId NOT MEMBER OF u.userRoles')
            ->setParameter('mobileId', $mobileId)
            ->setParameter('superAdminId', $superAdminId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all mobile users
     *
     * @return An array of mobile users
     */
    public function findAllMobileUsers()
    {
        $roleId = $this->getRoleId('ROLE_USER_MOBILE');
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('Core\Library\EntityBundle\Entity\User', 'u')
            ->join('u.userRoles', 'r')
            ->where(':roleId MEMBER OF u.userRoles')
            ->getQuery()
            ->setParameter('roleId', $roleId)
            ->getResult();
    }

    /**
     * Verify if a user is the owner of some package
     *
     * @param $userId The user id to check the ownership
     * @return bool True if the user is the owner of one or more packages
     */
    public function hasPackages($userId)
    {
        $packagesCount = $this->_em->createQueryBuilder()
            ->select('count(p.id)')
            ->from('Core\Library\EntityBundle\Entity\Package', 'p')
            ->where('p.user = :userId')
            ->getQuery()
            ->setParameter('userId', $userId)
            ->getSingleScalarResult();

        return $packagesCount > 0;
    }

    /**
     * Updates a detached user
     *
     * @param $user the user to be updated
     */
    public function update($user)
    {
        $this->_em->merge($user);
        $this->_em->flush();
    }

    /**
     * Delete a user by id
     *
     * @param $userId The id of the user to be deleted
     */
    public function delete($userId)
    {
        $user = $this->find($userId);
        $this->_em->remove($user);
        $this->_em->flush();
    }

    /**
     * Returns the query builder to get all admins and content managers
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllPortalUsersQueryBuilder()
    {
        $adminId = $this->getRoleId('ROLE_USER_ADMIN');
        $contentManagerId = $this->getRoleId('ROLE_USER_CONTENT_MANAGER');

        return $this->createQueryBuilder('u')
            ->join('u.organisation', 'o')
            ->where(':adminId MEMBER OF u.userRoles OR :contentManagerId MEMBER OF u.userRoles')
            ->orderBy('o.name, u.username', 'ASC')
            ->setParameter('adminId', $adminId)
            ->setParameter('contentManagerId', $contentManagerId);
    }
}