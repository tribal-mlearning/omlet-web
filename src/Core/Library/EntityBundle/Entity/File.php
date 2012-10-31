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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="files")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    public $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    public $mimeType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $md5sum;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;

    /**
     * @var virtual $file
     *
     * @Assert\File
     */
    public $file;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $size;

    /**
     * @ORM\Column(type="boolean")
     */
    public $public;

    public function __construct($file = null, $public = true)
    {
        $this->file = $file;
        $this->size = round(filesize($file) / 1024, 1);
        $this->created = new \DateTime();
        $this->public = $public;
    }

    /**
     * Lifecycle callback used to pre-set information
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            $this->name = $this->file->getClientOriginalName();
            $this->mimeType = $this->file->getMimeType();
            $this->path = uniqid() . '.' . $this->file->guessExtension();
            $this->setMD5sum(hash('md5', file_get_contents($this->file->getRealPath())));
        }
    }

    /**
     * Lifecycle callback used to reallocate files
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * Lifecycle callback used to remove information
     *
     * @ORM\PreRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * Get the absolute path of a file
     *
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    /**
     * Get the web path of a file
     *
     * @return null|string
     */
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * Get the upload root directory
     *
     * @return string
     */
    public function getUploadRootDir()
    {
        $rootDir = $this->public ? '/../../../../../web/' : '/../../../../../';

        return __DIR__ . $rootDir . $this->getUploadDir();
    }

    /**
     * Get the upload directory
     *
     * @return string
     */
    protected function getUploadDir()
    {
        return 'files';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Defines the MD5 sum
     *
     * @param $md5
     */
    public function setMD5sum($md5)
    {
        $this->md5sum = $md5;
    }

    /**
     * Get the MD5 sum
     *
     * @return string
     */
    public function getMD5sum()
    {
        return is_null($this->md5sum) ? '0' : $this->md5sum;
    }
}