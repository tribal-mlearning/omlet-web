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
namespace Core\Library\EntityBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Core\Library\EntityBundle\Entity\Role
 *
 * @ORM\Table(name="roles")
 * @ORM\Entity(repositoryClass="Core\Library\EntityBundle\EntityRepository\RoleRepository")
 */
class Role implements RoleInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $alias
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable=true)
     */
    protected $alias;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="userRoles")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="Permission", inversedBy="roles", cascade={"persist", "update"})
     * @ORM\JoinTable(name="permissions_roles",
     *     joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
     * )
     */
    private $permissions;

    public function __construct($name = null)
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Gets users
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add a user
     *
     * @param \Core\Library\EntityBundle\Entity\User A user
     */
    public function addUsers(\Core\Library\EntityBundle\Entity\User $user)
    {
        $this->users[] = $user;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = strtoupper($name);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->getName();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param Permission $permission
     */
    public function addPermission(\Core\Library\EntityBundle\Entity\Permission $permission)
    {
        $this->permissions[] = $permission;
    }

    /**
     * @param Permission $permission
     */
    public function delPermission(\Core\Library\EntityBundle\Entity\Permission $permission)
    {
        foreach ($this->permissions as $key=> $tmp) {
            if ($tmp->getId() == $permission->getId()) {
                unset($this->permissions[$key]);
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function getPermissionsArray()
    {
        $permissions = $this->getPermissions();
        $permsArray = array();
        foreach ($permissions as $permission) {
            /* @var $permission \Core\Library\EntityBundle\Entity\Permission */
            $permsArray[] = $permission->getName();
        }
        return $permsArray;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        if (is_null($this->alias)) return $this->getName();
        return $this->alias;
    }
}