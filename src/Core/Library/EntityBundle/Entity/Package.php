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

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Mapping as ORM;
use Core\Library\EntityBundle\Entity\User;
use Core\Library\EntityBundle\Entity\File;
use Core\Library\EntityBundle\Entity\Image;
use Symfony\Component\Validator\Constraints as Assert;
use Core\Library\EntityBundle\Constraints as CustomAssert;

/**
 * Core\Library\EntityBundle\Entity\Package
 *
 * @CustomAssert\PackageStructure()
 * @CustomAssert\PublishConstraint()
 * @ORM\Table(name="packages")
 * @ORM\Entity(repositoryClass="Core\Library\EntityBundle\EntityRepository\PackageRepository")
 */
class Package
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="unique_id", type="string", length=100)
     */
    private $uniqueId;

    /**
     * @ORM\Column(name="title", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="string", length=300)
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(name="published", type="boolean")
     */
    private $published = false;

    /**
     * @Assert\DateTime()
     * @ORM\Column(name="publish_start", type="datetime", nullable=true)
     */
    private $publishStart;

    /**
     * @ORM\ManyToOne(targetEntity="Core\Library\EntityBundle\Entity\User", inversedBy="packages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="PackageFile", mappedBy="package", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $files;

    /**
     * @ORM\OneToOne(targetEntity="File", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $thumbnail;

    /**
     * @Assert\Image(maxSize="1M", mimeTypes={"image/png", "image/jpeg", "image/gif"})
     */
    public $thumbnailContent;

    /**
     * @Assert\NotNull()
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="CourseCategory", inversedBy="packages")
     * @ORM\JoinTable(name="coursecategories_packages",
     *     joinColumns={@ORM\JoinColumn(name="package_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     */
    private $categories;

    /**
     * @CustomAssert\UniqueMetadata()
     * @ORM\ManyToMany(targetEntity="Metadata", inversedBy="packages", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="metadatas_packages",
     * joinColumns={@ORM\JoinColumn(name="package_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="metadata_id", referencedColumnName="id")}
     * )
     */
    private $metadata;

    public function __construct()
    {
        $this->setUniqueId(0);
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->metadata = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return mixed
     */
    public function getPublishStart()
    {
        return $this->publishStart;
    }

    /**
     * @param $publishStart
     */
    public function setPublishStart($publishStart)
    {
        $this->publishStart = $publishStart;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $file_id
     *
     * @return mixed
     * @throws \Exception If a package doesn' exists
     */
    public function getFile($file_id)
    {
        foreach ($this->files as $file)
            if ($file_id == $file->getId()) return $file;

        throw new \Exception("Package file does not exist");
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param File $thumbnail
     */
    public function setThumbnail(File $thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return mixed
     */
    public function getThumbnailContent()
    {
        return $this->thumbnailContent;
    }

    /**
     * @param $thumbnailContent
     */
    public function setThumbnailContent($thumbnailContent)
    {
        $this->thumbnailContent = $thumbnailContent;
        $this->setThumbnail(new File($thumbnailContent, true));
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param $value
     */
    public function setUniqueId($value)
    {
        $this->uniqueId = $value;
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @param Metadata $metadata
     */
    public function addMetadata(Metadata $metadata)
    {
        $this->getMetadata()->add($metadata);
    }

    /**
     * Used to generate a unique id for a new created package
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Package) {
            $entity->setUniqueId($entity->getId() . uniqid());
            $em = $args->getEntityManager();
            $em->persist($entity);
        }
    }

    /**
     * Used to create the right relationship with files
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        /** @var $entity Package */
        $entity = $args->getEntity();

        if ($entity instanceof Package) {
            $em = $args->getEntityManager();
            foreach ($entity->getFiles() as $file) {
                /** @var $file PackageFile */
                $file->setPackage($entity);
            }
            $em->persist($entity);
        }
    }

    /**
     * @param CourseCategory $category
     */
    public function addToCategory(CourseCategory $category)
    {
        if (!$this->getCategories()->contains($category)) {
            $this->getCategories()->add($category);
        }
    }

    /**
     * @param CourseCategory $category
     */
    public function delFromCategory(CourseCategory $category)
    {
        $this->getCategories()->removeElement($category);
    }

    /**
     * @param $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getCategoriesArray()
    {
        $categories = $this->getCategories();
        $catArray = array();
        foreach ($categories as $category) {
            /** @var $category CourseCategory */
            $catArray[] = $category->getId();
        }
        return $catArray;
    }

    /**
     * @return string
     */
    public function getMetadataDescription()
    {
        if (empty($this->metadata)) return "";

        $metadata_array = array();
        foreach ($this->metadata as $metadata) {
            $metadata_array[] = $metadata->getName() . "=" . $metadata->getValue();
        }

        return implode(', ', $metadata_array);
    }
}