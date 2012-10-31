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
namespace Service\ImageBundle\Services;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Monolog\Logger;

class ImagesService
{
    protected $entityLibraryService;
    protected $validatorService;
    protected $securityContext;
    protected $loggerService;
    protected $em;

    public function __construct(Logger $loggerService, EntityLibrary $entityLibraryService, SecurityContext $securityContext, Validator $validatorService)
    {
        $this->entityLibraryService = $entityLibraryService;
        $this->validatorService = $validatorService;
        $this->securityContext = $securityContext;
        $this->loggerService = $loggerService;
        $this->em = $entityLibraryService->getManager();
    }

    /**
     * Resize an image (JPEG/PNG) to default size
     *
     * @param $path Image path to be resized
     */
    public function resize($path)
    {
        if (is_null($path)) return;

        $format = $this->getImageFormat($path);
        $thumbSize = $this->getImageSize($path);
        switch ($format) {
            case 'image/jpeg':
                $this->resizeJPEG($path);
                break;
            case 'image/png':
                $this->resizePNG($path);
                break;
        }
    }

    /**
     * Resize a JPEG
     *
     * @param $absolutePath The image absolute path
     * @param array $options The options array (supported values: width, height, keepAspectRatio)
     * @param null $newTargetPath The output image path
     */
    public function resizeJPEG($absolutePath, $options = array(), $newTargetPath = null)
    {
        list($width, $height) = getimagesize($absolutePath);
        $source = imagecreatefromjpeg($absolutePath);
        $newwidth = isset($options['width']) ? $options['width'] : 128;
        $newheight = isset($options['height']) ? $options['height'] : 128;
        if (!isset($options['keepAspectRatio']) || $options['keepAspectRatio'] == false) {
            if ($width > $height) { // landscape
                $newheight = $height / $width * $newheight;
            } else {
                $newwidth = $width / $height * $newwidth;
            }
        }
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        if (is_null($newTargetPath))
            imagejpeg($thumb, $absolutePath);
        else
            imagejpeg($thumb, $newTargetPath);
    }

    /**
     * Resize a PNG
     *
     * @param $absolutePath The image absolute path
     * @param array $options The options array (supported values: width, height, keepAspectRatio)
     * @param null $newTargetPath The output image path
     */
    public function resizePNG($absolutePath, $options = array(), $newTargetPath = null)
    {
        list($width, $height) = getimagesize($absolutePath);
        $source = imagecreatefrompng($absolutePath);
        $newwidth = isset($options['width']) ? $options['width'] : 128;
        $newheight = isset($options['height']) ? $options['height'] : 128;
        if (!isset($options['keepAspectRatio']) || $options['keepAspectRatio'] == false) {
            if ($width > $height) { // landscape
                $newheight = $height / $width * $newheight;
            } else {
                $newwidth = $width / $height * $newwidth;
            }
        }

        $thumb = imagecreatetruecolor($newwidth, $newheight);
        imagealphablending($thumb, false);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefilledrectangle($thumb, 0, 0, $newwidth, $newheight, $transparent);
        imagealphablending($thumb, true);

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        if (is_null($newTargetPath))
            imagepng($thumb, $absolutePath);
        else
            imagepng($thumb, $newTargetPath);
    }

    /**
     * Gets the image size
     * @param $absolutePath The image absolute path
     * @return array Array with the image dimensions
     */
    public function getImageSize($absolutePath)
    {
        list($width, $height) = getimagesize($absolutePath);
        return array('width'  => $width,
                     'height' => $height);
    }

    /**
     * Get the image format
     *
     * @param $absolutePath The image absolute path
     * @return string The mimetype
     */
    public function getImageFormat($absolutePath)
    {
        $info = getimagesize($absolutePath);
        return $info['mime'];
    }
}
