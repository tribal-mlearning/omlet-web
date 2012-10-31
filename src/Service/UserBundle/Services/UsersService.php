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
namespace Service\UserBundle\Services;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Monolog\Logger;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Core\Library\EntityBundle\Entity\User;
use Frontend\PortalBundle\Util\PinMatcher;

class UsersService
{

    /**
     * @var EntityLibrary
     */
    protected $entityLibraryService;
    /**
     * @var Validator
     */
    protected $validatorService;
    /**
     * @var SecurityContext
     */
    protected $securityContext;
    /**
     * @var Logger
     */
    protected $loggerService;
    /**
     * @var EncoderFactory
     */
    protected $factory;
    /**
     * @var Translator
     */
    protected $translatorService;

    public function __construct(Logger $loggerService, EntityLibrary $entityLibraryService, SecurityContext $securityContext, Validator $validatorService, EncoderFactory $factory, Translator $translatorService)
    {
        $this->entityLibraryService = $entityLibraryService;
        $this->validatorService = $validatorService;
        $this->securityContext = $securityContext;
        $this->loggerService = $loggerService;
        $this->factory = $factory;
        $this->em = $entityLibraryService->getManager();
        $this->translatorService = $translatorService;
    }

    /**
     * Find a user by Id
     *
     * @param $id
     *
     * @return object The found user
     * @throws \Exception If there is no user with that id
     */public function find($id)
    {
        $user = $this->entityLibraryService->get('User')->find($id);

        if (is_null($user))
            throw new \Exception($this->translatorService->trans('message.find.fail', array(), 'user'));

        return $user;
    }

    /**
     * Try to discover the country of a user based on his username
     * If it doesn't exists, create an unknown one
     *
     * @param $pinNumber The user name
     * @return mixed|object The country
     */public function getCountryFromPinNumber($pinNumber)
    {
        $countryCode = substr($pinNumber, -2);
        $entity = $this->entityLibraryService->get('Country')->findOneBy(array('code' => $countryCode));
        if (!$entity) {
            $entity = $this->entityLibraryService->getNew('Country');
            $entity->setCode($pinNumber);
            $entity->setName('Unknown');
            $entity->setLocale('en_US');
        }
        return $entity;
    }

    /**
     * Check if the user identified by userId is the owner of some package
     *
     * @param $userId The user to check ownership
     * @return bool true if it has packages, false otherwise
     */public function hasPackages($userId)
    {
        return $this->entityLibraryService->get('User')->hasPackages($userId);
    }

    /**
     * Delete the user identified by userId
     *
     * @param $userId The user to be deleted
     * @return bool if the user was deleted
     * @throws \Exception If the user has packages or its logged in
     */public function delete($userId)
    {
        if ($userId == $this->securityContext->getToken()->getUser()->getId()) {
            throw new \Exception($this->translatorService->trans('message.delete.fail.user_is_logged_in', array(), 'user'));
        }

        if ($this->hasPackages($userId))
            throw new \Exception($this->translatorService->trans('message.delete.fail.user_has_packages', array(), 'user'));

        return $this->entityLibraryService->get('User')->delete($userId);
    }

    /**
     * @param $currentId
     * @param $newId
     * @return mixed
     */public function updatePackagesAndDelete($currentId, $newId)
    {
        $this->entityLibraryService->get('Package')->updatePackagesOwner($currentId, $newId);
        try {
            return $this->delete($currentId);
        } catch (Exception $e) {
            $this->entityLibraryService->get('Package')->updatePackagesOwner($newId, $currentId);
            echo $this->translatorService->trans('message.delete.fail', array("{{currentId}}" => $currentId,
                                                                              "{{message}}"   => $e->getMessage()), 'user');
        }
    }

    /**
     * @param $current
     * @param $new
     * @param $verification
     * @throws \Exception
     */public function updatePassword($current, $new, $verification)
    {
        $user = clone $this->securityContext->getToken()->getUser();

        $encoder = $this->factory->getEncoder($user);
        $encryptedCurrent = $encoder->encodePassword($current, $user->getSalt());

        if ($user->getPassword() != $encryptedCurrent) {
            throw new \Exception($this->translatorService->trans('message.update.password.fail.not_matching.current', array(), 'user'));
        }

        if (strlen($new) < 6) {
            throw new \Exception($this->translatorService->trans('message.update.password.fail.size', array(), 'user'));
        }

        if ($new != $verification) {
            throw new \Exception($this->translatorService->trans('message.update.password.fail.not_matching.verification', array(), 'user'));
        }

        $encryptedNew = $encoder->encodePassword($new, $user->getSalt());
        $user->setPassword($encryptedNew);

        $this->entityLibraryService->get('User')->update($user);
    }

    /**
     * @param $userId
     * @return mixed
     */public function getSubstitutePackageOwners($userId)
    {
        return $this->entityLibraryService->get('User')->findSubstitutePackageOwners($userId);
    }

    /**
     * @param $usernames
     * @return array
     */public function getExistingUsernames($usernames)
    {
        if (empty($usernames))
            return array();

        $query = $this->em->createQuery('SELECT u.username FROM Core\Library\EntityBundle\Entity\User u WHERE u.username IN (:usernames)');
        $query->setParameter('usernames', $usernames);
        $rows = $query->getArrayResult();
        $usernames = array();

        foreach ($rows as $row) {
            array_push($usernames, $row['username']);
        }

        return $usernames;
    }

    /**
     * @param $users
     */public function persistUsers($users)
    {
        $role = $this->entityLibraryService->get('Role')->findOneByName('ROLE_USER_MOBILE');

        foreach ($users as $user) {
            $user->addUserRoles($role);
            $user->setHashId(hash('sha512', $user->getUsername() . '{' . $user->getSalt() . '}'));
            $this->em->persist($user);
        }

        $this->em->flush();
    }

    /**
     * @param \Core\Library\EntityBundle\Entity\User $user
     */
    public function persistMobileUser(User $user)
    {

        $role = $this->entityLibraryService->get('Role')->findOneByName('ROLE_USER_MOBILE');
        $user->addUserRoles($role);
        $user->setHashId(hash('sha512', $user->getUsername() . '{' . $user->getSalt() . '}'));
        $encoder = $this->factory->getEncoder($user);
        $encryptedPass = $encoder->encodePassword('123456', $user->getSalt());
        $user->setPassword($encryptedPass);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @param $receivedPins
     * @return array
     */public function getValidPins($receivedPins)
    {
        $pins = array_unique(explode("\r\n", $receivedPins));
        $validPins = array();
        foreach ($pins as $pin) {
            if (PinMatcher::isValid($pin))
                array_push($validPins, $pin);
        }
        return $validPins;
    }

    /**
     * @param $receivedPins
     * @return array
     */public function getInvalidPins($receivedPins)
    {
        $pins = array_unique(explode("\r\n", $receivedPins));
        $invalidPins = array();
        foreach ($pins as $pin) {
            if (!PinMatcher::isValid($pin))
                array_push($invalidPins, $pin);
        }
        return $invalidPins;
    }
}