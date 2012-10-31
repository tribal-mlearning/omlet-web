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

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Core\Library\EntityBundle\Entity\Organisation;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Core\Library\EntityBundle\Constraints as CustomAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Core\Library\EntityBundle\Entity\User
 *
 * @ORM\Table(name="users",indexes={@ORM\index(name="search_idx", columns={"username"})})
 * @ORM\Entity(repositoryClass="Core\Library\EntityBundle\EntityRepository\UserRepository")
 * @UniqueEntity("username")
 */
class User implements UserInterface, \Serializable
{
    const Preactivation = 'P';
    const Activated = 'A';
    const Disabled = 'D';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @CustomAssert\Pin(groups={"createMobileUser", "updateMobileUser"})
     */
    private $username;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(name="hash_id", type="string", length=255)
     */
    private $hashId;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\MinLength(limit=6)
     */
    private $password;

    /**
     * @ORM\Column(type="datetime", name="created_at", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     */
    protected $updatedAt;

    /**
     * @CustomAssert\Count(min=1, groups={"create", "update"})
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users", cascade={"persist"})
     * @ORM\JoinTable(name="users_roles",
     * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $userRoles;

    /**
     * @ORM\ManyToMany(targetEntity="Metadata", inversedBy="users", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="metadatas_users",
     * joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="metadata_id", referencedColumnName="id")}
     * )
     */
    protected $userMetadatas;

    /**
     * @ORM\OneToMany(targetEntity="Package", mappedBy="user")
     **/
    protected $packages;

    /**
     * @ORM\ManyToOne(targetEntity="Core\Library\EntityBundle\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"webUser"})
     */
    private $organisation;

    /**
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private $state = User::Preactivation;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->userMetadatas = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->packages = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $hashId
     */
    public function setHashId($hashId)
    {
        $this->hashId = $hashId;
    }

    /**
     * @return mixed
     */
    public function getHashId()
    {
        return $this->hashId;
    }

    /**
     * @param $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * @param Role $role
     */
    public function addUserRoles(\Core\Library\EntityBundle\Entity\Role $role)
    {
        $this->userRoles[] = $role;
    }

    /**
     * @return mixed
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * @param Track $track
     */
    public function addTrack(\Core\Library\EntityBundle\Entity\Track $track)
    {
        $this->tracks[] = $track;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param Package $package
     */
    public function addPackage(\Core\Library\EntityBundle\Entity\Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * @return mixed
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserMetadatas()
    {
        return $this->userMetadatas;
    }

    /**
     * @param $metadatas
     */
    public function setUserMetadatas($metadatas)
    {
        $this->userMetadatas = $metadatas;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->getUserRoles()->toArray();
    }

    /**
     * @return string
     */
    public function getUserRolesDescription()
    {
        $roleNames = array();
        foreach ($this->userRoles as $role) {
            array_push($roleNames, $role->getName());
        }
        return implode(', ', $roleNames);
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return 'a0fk04383ruaf98b7a7afg76523';
    }

    /**
     *
     */
    public function eraseCredentials()
    {
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return bool
     */
    public function equals(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }
        return (trim(strtolower($user->getUsername())) == trim(strtolower($this->username)));
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->getUsername()
        ));
    }

    /**
     * @param $serialized
     */
    public function unserialize($serialized)
    {
        $arr = unserialize($serialized);
        $this->setUsername($arr[0]);
    }

    /**
     * @return string
     */
    public function getUsernameOrganisation()
    {
        return $this->username . " (" . $this->organisation->getName() . ")";
    }
}