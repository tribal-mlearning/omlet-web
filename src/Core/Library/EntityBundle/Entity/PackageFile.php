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
use Core\Library\EntityBundle\Entity\User;
use Core\Library\EntityBundle\Entity\File;
use Core\Library\EntityBundle\Entity\Image;
use Symfony\Component\Validator\Constraints as Assert;
use Core\Library\EntityBundle\Constraints as CustomAssert;

/**
 * Core\Library\EntityBundle\Entity\PackageFile
 *
 * @CustomAssert\PackageFileNotNull()
 * @ORM\Table(name="packagefiles")
 * @ORM\Entity()
 */
class PackageFile
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="File", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $file;

    /**
     * @Assert\File(mimeTypes={"application/zip"})
     */
    private $fileContent;

    /**
     * @ORM\ManyToOne(targetEntity="Package", inversedBy="files")
     */
    private $package;

    /**
     * @ORM\Column(name="version", type="string")
     * @Assert\NotBlank()
     */
    private $version;

    /**
     * @CustomAssert\UniqueMetadata()
     * @ORM\ManyToMany(targetEntity="Metadata", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="metadatas_packagefiles",
     * joinColumns={@ORM\JoinColumn(name="packagefile_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="metadata_id", referencedColumnName="id")}
     * )
     *
     */
    private $metadata;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFileContent()
    {
        return $this->fileContent;
    }

    /**
     * @param $fileContent
     */
    public function setFileContent($fileContent)
    {
        $this->fileContent = $fileContent;
        $this->setFile(new File($fileContent, false));
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param $package
     */
    public function setPackage($package)
    {
        $this->package = $package;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->file == null ? null : $this->file->getName();
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