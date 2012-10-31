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
namespace Core\Security\AccessControlBundle\TwigExtensions;

use Symfony\Component\Translation\TranslatorInterface;
use Core\Security\AccessControlBundle\Services\AccessControlService;

class AccessControlExtension extends \Twig_Extension
{
    protected $accessControl;

    public function __construct(AccessControlService $accessControlService)
    {
        $this->accessControl = $accessControlService;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            'hasPermissions' => new \Twig_Function_Method($this, 'hasPermissions'),
        );
    }

    public function getName()
    {
        return 'access_control_extension';
    }

    /**
     * Twig extension method to check if the current authenticated user has necessary permissions
     *
     * @param array|string      $permissions A single permission or an array of permissions to check access
     * @param string            $context     (optional) The context which permissions are being validated to, defaults to: SYSTEM
     *
     * @return bool
     */
    public function hasPermissions($permissions = array(), $context = null)
    {
        $isGranted = $this->accessControl->hasPermissions($permissions, $context);
        return $isGranted;
    }
}