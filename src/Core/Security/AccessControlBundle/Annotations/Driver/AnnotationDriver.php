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
namespace Core\Security\AccessControlBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Core\Security\AccessControlBundle\Services\AccessControlService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AnnotationDriver
{
    private $reader;
    private $accessControlService;

    public function __construct(Reader $reader, AccessControlService $accessControlService)
    {
        $this->reader = $reader;
        $this->accessControlService = $accessControlService;
    }

    /**
     * Intercept all controller calls to check if there is a Permissions annotation on it.
     * If so, use the access control service to validate if the current logged user has the
     * right permissions to access that controller action.
     *
     * The available annotation properties are: list and context
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     *
     * @return mixed If there is no controller
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException If the current logged user doesn't have the right credentials
     */
    public function onKernelController(FilterControllerEvent $event)
    {

        if (!is_array($controller = $event->getController())) {
            return; // No controller
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if (isset($configuration->list)) {
                $permissionsList = $configuration->list;
                if ($this->accessControlService->hasPermissions($permissionsList, $configuration->context) == false) {
                    throw new AccessDeniedHttpException();
                }
            }
        }
    }
}