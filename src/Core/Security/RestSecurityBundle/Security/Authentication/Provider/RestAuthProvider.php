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
namespace Core\Security\RestSecurityBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Core\Security\RestSecurityBundle\Security\Authentication\Token\RestAuthUserToken;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Core\Library\EntityBundle\Entity\User;
use Monolog\Logger;

class RestAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cacheDir;
    private $expires;
    private $entityLibraryService;
    private $factory;

    public function __construct(UserProviderInterface $userProvider, $cacheDir, EntityLibrary $entityLibraryService, $expires, Logger $loggerService, EncoderFactory $factory)
    {
        $this->userProvider = $userProvider;
        $this->cacheDir = $cacheDir;
        $this->entityLibraryService = $entityLibraryService;
        $this->expires = $expires;
        $this->loggerService = $loggerService;
        $this->factory = $factory;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir);
        }
    }

    /**
     * Given a RestAuthUserToken, try to authenticate an active user against the system
     * using the WSSE strategy.
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return \Core\Security\RestSecurityBundle\Security\Authentication\Token\RestAuthUserToken
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->entityLibraryService->get('User')->findOneBy(array('hashId' => $token->hashId));

        if (!is_null($user) && $this->validateAccess($token->accessToken, $token->nonce, $token->created, $user->getPassword()) && $user->getState() == User::Activated) {
            $authenticatedToken = new RestAuthUserToken($user->getRoles());
            $authenticatedToken->setUser($user);
            $authenticatedToken->hashId = $token->hashId;
            $authenticatedToken->setAuthenticated(true);
            $this->loggerService->debug('Authentication successful', array($authenticatedToken));
            return $authenticatedToken;
        }

        $this->loggerService->debug('Authentication failed', array($token));
        throw new AuthenticationException('Authentication failed');
    }

    /**
     * Try to validate the user provided accessToken
     *
     * @param $accessToken The access token
     * @param $nonce The unique once
     * @param $created The create time
     * @param $password User hashed password
     *
     * @return bool True if the access token is valid
     *
     * @throws \Symfony\Component\Security\Core\Exception\NonceExpiredException If the nonce creation time is higher than expires property
     */
    protected function validateAccess($accessToken, $nonce, $created, $password)
    {
        $expires = $this->expires;
        $currentTime = time();
        if ($currentTime - $created > $expires) {
            return;
        }
        if (file_exists($this->cacheDir . '/' . $nonce) && file_get_contents($this->cacheDir . '/' . $nonce) + $expires >= $currentTime) {
            throw new NonceExpiredException('Previously used nonce detected');
        }
        file_put_contents($this->cacheDir . '/' . $nonce, $currentTime);
        $expected = base64_encode(hash('sha512', base64_decode($nonce) . $created . $password));

        $this->loggerService->debug('Validation of AccessToken against ExpectedToken', array($accessToken, $expected));
        return $accessToken === $expected;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof RestAuthUserToken;
    }
}