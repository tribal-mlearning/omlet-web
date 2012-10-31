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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class RoleRepository extends EntityRepository
{
    /**
     * Get all roles from a user
     *
     * @param $userId The id of the user
     *
     * @return array An array of Roles
     */
    public function getAllRolesFromUser($userId)
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.users', 'u')
            ->where('u.id = :userId');

        $qb->setParameter('userId', $userId);
        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    /**
     * Get all permissions from a role
     *
     * @param $roleId The role id
     * @return array An array of permissions
     */
    public function getAllPermissions($roleId)
    {
        $query = $this->_em->createQuery('
            SELECT p
            FROM Core\Library\EntityBundle\Entity\Permission p
            JOIN p.roles r
            WHERE r.id = :roleId
        ');
        $query->setParameter('roleId', $roleId);
        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * Delete a role
     *
     * @param $roleId The role id to be deleted
     */
    public function delete($roleId)
    {
        $role = $this->find($roleId);
        $this->_em->remove($role);
        $this->_em->flush();
    }
}