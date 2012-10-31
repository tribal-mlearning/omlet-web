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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="coursecategories",indexes={@ORM\index(name="search_idx", columns={"id"})})
 * @ORM\Entity(repositoryClass="Core\Library\EntityBundle\EntityRepository\CourseCategoryRepository")
 */
class CourseCategory
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="CourseCategory", inversedBy="children", cascade={"persist", "update"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="CourseCategory", mappedBy="parent", cascade={"all"})
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Package", mappedBy="categories")
     */
    private $packages;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return CourseCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function __toString()
    {
        $currentCategory = $this;
        $hierarchy = $currentCategory->getName();

        while ($currentCategory->getParent() != null) {
            $currentCategory = $currentCategory->getParent();
            $hierarchy = $currentCategory->getName() . '/' . $hierarchy;
        }

        return $hierarchy;
    }

    /**
     * @param CourseCategory $child
     */
    public function addChild(CourseCategory $child)
    {
        $child->setParent($this);
        $this->getChildren()->add($child);
    }

    /**
     * @param CourseCategory $child
     *
     * @return bool
     */
    public function hasChild(CourseCategory $child)
    {
        return $this->getChildren()->contains($child);
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return ($this->getChildren()->count() > 0);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param Package $package
     */
    public function addPackage(Package $package)
    {
        $package->addToCategory($this);
        $this->getPackages()->add($package);
    }

    /**
     * @param Package $package
     */
    public function delPackage(Package $package)
    {
        $package->delFromCategory($this);
        $this->getPackages()->removeElement($package);
    }

    /**
     * @static
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    public static function compare($a, $b)
    {
        return strcmp($a->getName(), $b->getName());
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPackages()
    {
        return $this->packages;
    }
}