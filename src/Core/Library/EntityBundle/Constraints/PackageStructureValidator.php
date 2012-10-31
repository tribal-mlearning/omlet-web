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
namespace Core\Library\EntityBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class PackageStructureValidator extends ConstraintValidator
{
    protected $zipService;
    protected $translatorService;
    private $rootFolders = 'root_folders';
    private $hasVersion = 'has_version';
    private $xmlFileIndex = 'xml_file_index';

    public function __construct(\Service\PackageBundle\Services\ZipService $zipService, Translator $translatorService)
    {
        $this->zipService = $zipService;
        $this->translatorService = $translatorService;
    }

    /**
     * Used to validate the whole structure of an uploaded package
     *
     * @param mixed $value The value
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint
     * @return bool The validation status
     */
    public function isValid($value, Constraint $constraint)
    {
        if (is_null($value->getUniqueId())) return true;
        $isValid = true;
        foreach ($value->getFiles() as $index => $file)
            $isValid &= $this->isValidZipFile($file, $value->getUniqueId(), $index);
        return $isValid;
    }

    /**
     * Inspect a package uploaded file an do all the validations needed to it
     *
     * @param $file The file
     * @param $packageUniqueId The package unique id to validate against
     * @param $index The file index
     *
     * @return bool Return true if its valid
     */
    private function isValidZipFile($file, $packageUniqueId, $index)
    {
        $fileContent = $file->getFileContent();
        if ($fileContent == null || $fileContent->getMimeType() != 'application/zip') return true;

        $this->zipService->load($fileContent);
        $list = $this->zipService->listContent();

        //First step is try to find the package.version and the package.xml in
        //the root of the zip file.
        $structure = $this->inspectPackageStructure($list);

        if ($this->hasVersionAndXmlFiles($structure)) {
            return $this->isRightUniqueId($packageUniqueId, $structure[$this->xmlFileIndex], $index);
        }

        if ($this->hasZeroRootFolders($structure)) {
            return $this->addViolation($this->translatorService->trans("validation.package.required_files"), $index);
        }

        //The second step is try to find the package.version and the package.xml 
        //within the root folder in the zip file. But it is valid only if the 
        //zip file has just one folder at its root.
        if ($this->hasMultipleRootFolders($structure)) {
            return $this->addViolation($this->translatorService->trans("validation.package.multiple_root_folders"), $index);
        }

        $structure = $this->inspectPackageStructure($list, $list[$structure[$this->rootFolders][0]]['filename'], false);
        if ($this->hasVersionAndXmlFiles($structure)) {
            return $this->isRightUniqueId($packageUniqueId, $structure[$this->xmlFileIndex], $index);
        }

        return $this->addViolation($this->translatorService->trans("validation.package.required_files"), $index);
    }

    /**
     * Inspect the Package structure to see if it has the right nodes to be verified
     *
     * @param        $baseArray Unziped content list
     * @param string $baseString File base string
     * @param bool   $lookForRootFolders To decide whether to check the root folders
     *
     * @return array Inspected package structure
     */
    private function inspectPackageStructure($baseArray, $baseString = "", $lookForRootFolders = true)
    {
        $return = array($this->hasVersion   => false,
                        $this->xmlFileIndex => -1,
                        $this->rootFolders  => array());

        if (sizeof($baseArray) == 0) return $return;

        for ($i = 0; $i < sizeof($baseArray); $i++) {
            if ($baseArray[$i]['folder']) {
                if ($lookForRootFolders && $this->isRootFolder($baseArray[$i]['filename'])) {
                    array_push($return[$this->rootFolders], $i);
                }
            }
            else {
                if ($baseArray[$i]['filename'] == $baseString . 'package.xml') {
                    $return[$this->xmlFileIndex] = $i;
                    if ($return[$this->hasVersion]) return $return;
                    continue;
                }
                if ($baseArray[$i]['filename'] == $baseString . 'package.version') {
                    $return[$this->hasVersion] = true;
                    if ($return[$this->xmlFileIndex] > -1) return $return;
                    continue;
                }
            }
        }
        return $return;
    }

    /**
     * Add a violation message for a specific field when needed
     *
     * @param $message The message
     * @param $index The field index
     *
     * @return bool False because it's a violation
     */
    private function addViolation($message, $index)
    {
        $propertyPath = 'data.files[' . $index . '].fileContent';
        $this->context->setPropertyPath($propertyPath);
        $this->context->addViolation($message, array(), null);
        return false;
    }

    /**
     * Check if it' the root folder
     *
     * @param $folderPath The path to the folder to be checked
     * @return bool True if its the root
     */
    private function isRootFolder($folderPath)
    {
        return sizeof(preg_split("/\//", $folderPath, -1, PREG_SPLIT_NO_EMPTY)) == 1;
    }

    /**
     * Validate if the filed has the package unique ID specified
     * @param $packageUniqueId The unique ID to validate against
     * @param $xmlIndex Node index
     * @param $index Field index
     * @return bool True if it's the right unique ID otherwise add a violation
     */
    private function isRightUniqueId($packageUniqueId, $xmlIndex, $index)
    {
        $fileArray = $this->zipService->extractByIndex($xmlIndex, PCLZIP_OPT_EXTRACT_AS_STRING);
        $fileContent = $fileArray[0]["content"];
        $xml = @simplexml_load_string($fileContent);

        if (empty($xml))
            return $this->addViolation($this->translatorService->trans("validation.package.unique_id"), $index);

        $packageTagId = (string)$xml->attributes()->id;

        if ($packageTagId !== $packageUniqueId)
            return $this->addViolation($this->translatorService->trans("validation.package.unique_id"), $index);

        return true;
    }

    /**
     * Check if has version and package xml files
     *
     * @param $structure The package file structure
     * @return bool If it has the right files
     */
    private function hasVersionAndXmlFiles($structure)
    {
        return $structure[$this->hasVersion] && $structure[$this->xmlFileIndex] > -1;
    }

    /**
     * Check if it doesn't have root folders
     *
     * @param $structure The package file structure
     * @return bool True if doesn't have the root folder
     */
    private function hasZeroRootFolders($structure)
    {
        return sizeof($structure[$this->rootFolders]) == 0;
    }

    /**
     * Check if it has multiple root folders
     *
     * @param $structure The package file structure
     * @return bool True if has more than one root folder
     */
    private function hasMultipleRootFolders($structure)
    {
        return sizeof($structure[$this->rootFolders]) > 1;
    }
}
