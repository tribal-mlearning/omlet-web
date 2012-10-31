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
namespace Core\Security\FormSecurityBundle\Handler;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Core\Library\EntityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Core\Security\AccessControlBundle\Services\AccessControlService;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected $router;
    protected $security;
    protected $translatorService;
    protected $accessControlService;

    public function __construct(Router $router, SecurityContext $security, Translator $translatorService, AccessControlService $accessControlService)
    {
        $this->router = $router;
        $this->security = $security;
        $this->translatorService = $translatorService;
        $this->accessControlService = $accessControlService;
    }

    /**
     * Additional handler that deny the access of an inactive user or,
     * a user without the right permissions to access the web suite.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $session = $request->getSession();

        $failed = false;
        if ($user->getState() != User::Activated) {
            $session->set(SecurityContext::AUTHENTICATION_ERROR, array('message' => $this->translatorService->trans('message.login.user_disabled', array(), 'login')));
            $failed = true;
        } else if (!$this->accessControlService->hasPermissions('ACCESS_WEB', 'SYSTEM')) {
            $session->set(SecurityContext::AUTHENTICATION_ERROR, array('message' => $this->translatorService->trans('message.login.no_privilege', array(), 'login')));
            $failed = true;
        }

        if ($failed) {
            return new RedirectResponse($this->router->generate('login'));
        } else {
            return new RedirectResponse($request->get('_target_path'));
        }
    }
}