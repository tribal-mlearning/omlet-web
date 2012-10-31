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
namespace Core\Security\RestSecurityBundle\Security\Firewall;

use Core\Library\EntityBundle\Entity\Role;
use Core\Library\EntityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Core\Security\RestSecurityBundle\Security\Authentication\Token\RestAuthUserToken;
use Monolog\Logger;
use Core\Security\AccessControlBundle\Services\AccessControlService;

class RestAuthListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    protected $loggerService;
    protected $accessControlService;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, Logger $loggerService, AccessControlService $accessControlService)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->loggerService = $loggerService;
        $this->accessControlService = $accessControlService;
    }

    /**
     * Listener to all service rest calls that create an authentication token
     * based in the X-AUTH header which has AuthToken parameters supplied inside of it.
     *
     * A valid and authenticated token can also throw a Access Denied if the user doesn't have
     * the right permissions to access the rest interface through mobile devices.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $headerExtensionName = 'x-auth';

        if (!$request->headers->has($headerExtensionName)) {
            $this->loggerService->debug('Auth header not present, using anonymous');
            $anonymousToken = new RestAuthUserToken(array(new Role('IS_AUTHENTICATED_ANONYMOUSLY')));
            $anonymousToken->setAuthenticated(true);
            return $this->securityContext->setToken($anonymousToken);
        }

        $this->loggerService->debug('Received auth header', array($request->headers->get($headerExtensionName)));

        $wsseRegex = '/AuthToken HashId="([^"]+)", AccessToken="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"/';
        $wsseRegexU = '/AuthToken UserId="([^"]+)", HashId="([^"]+)", AccessToken="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"/';

        $hasAuthToken = preg_match($wsseRegex, $request->headers->get($headerExtensionName), $matches);
        $hasUserParameter = false;
        if (empty($matches)) {
            $hasAuthToken = preg_match($wsseRegexU, $request->headers->get($headerExtensionName), $matches);
            $hasUserParameter = true;
        }

        if ($hasAuthToken) {
            $token = new RestAuthUserToken();
            if ($hasUserParameter) {
                $token->userId = $matches[1];
                $token->hashId = $matches[2];
                $token->accessToken = $matches[3];
                $token->nonce = $matches[4];
                $token->created = $matches[5];
            } else {
                $token->userId = null;
                $token->hashId = $matches[1];
                $token->accessToken = $matches[2];
                $token->nonce = $matches[3];
                $token->created = $matches[4];
            }

            try {
                $returnValue = $this->authenticationManager->authenticate($token);
                if ($returnValue instanceof TokenInterface) {
                    if (!$this->accessControlService->hasPermissions('ACCESS_MOBILE', 'SYSTEM', $returnValue->getUser())) {
                        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("This user doesn't have mobile access privileges");
                    }
                    return $this->securityContext->setToken($returnValue);
                } else if ($returnValue instanceof Response) {
                    return $event->setResponse($returnValue);
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }

        $response = new Response($message);
        $response->setStatusCode(403);
        $event->setResponse($response);
    }
}