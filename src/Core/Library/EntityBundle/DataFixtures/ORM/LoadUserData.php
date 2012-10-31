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
namespace Core\Library\EntityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Core\Library\EntityBundle\Entity\User;
use Core\Library\EntityBundle\Entity\Organisation;
use Core\Library\EntityBundle\Entity\Role;
use Core\Library\EntityBundle\Entity\PermissionContext;
use Core\Library\EntityBundle\Entity\Permission;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class LoadUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var $container ContainerInterface
     */
    private $container;

    /**
     * Load default users into the system
     *
     * @param $manager \Doctrine\Common\Persistence\ObjectManager
     */
    public function load($manager)
    {
        $organisation = $this->getReference('ORGANISATION_MOBILE');

        $adminUser = new User();
        $adminUser->setUsername('superadmin');
        $adminUser->setState(User::Activated);
        $adminUser->setPassword('mobile');
        $adminUser->setHashId(hash('sha512', 'mobile' . '{' . $adminUser->getSalt() . '}'));
        /** @var $factory \Symfony\Component\Security\Core\Encoder\EncoderFactory */
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($adminUser);
        $password = $encoder->encodePassword($adminUser->getPassword(), $adminUser->getSalt());
        $adminUser->setPassword($password);
        $adminUser->addUserRoles($this->getReference('ROLE_USER_SUPERADMIN'));
        $adminUser->setOrganisation($organisation);
        $adminUser->setState(User::Activated);
        $manager->persist($adminUser);
        $manager->flush();

        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setState(User::Activated);
        $adminUser->setPassword('mobile');
        $adminUser->setHashId(hash('sha512', 'mobile' . '{' . $adminUser->getSalt() . '}'));
        /** @var $factory \Symfony\Component\Security\Core\Encoder\EncoderFactory */
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($adminUser);
        $password = $encoder->encodePassword($adminUser->getPassword(), $adminUser->getSalt());
        $adminUser->setPassword($password);
        $adminUser->addUserRoles($this->getReference('ROLE_USER_ADMIN'));
        $adminUser->setOrganisation($organisation);
        $adminUser->setState(User::Activated);
        $manager->persist($adminUser);
        $manager->flush();

        $userPin = '123456';
        $newUser = new User();
        $newUser->setUsername($userPin);
        $newUser->setState(User::Activated);
        $newUser->setPassword('123456');
        $newUser->setHashId(hash('sha512', $userPin . '{' . $newUser->getSalt() . '}'));
        /** @var $factory \Symfony\Component\Security\Core\Encoder\EncoderFactory */
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($newUser);
        $password = $encoder->encodePassword($newUser->getPassword(), $newUser->getSalt());
        $newUser->setPassword($password);
        $newUser->addUserRoles($this->getReference('ROLE_USER_MOBILE'));
        $newUser->setState(User::Activated);
        $manager->persist($newUser);
        $manager->flush();

        for ($i = 0; $i < 2; $i++) {
            $userPin = 'dev' . $i;
            $newUser = new User();
            $newUser->setUsername($userPin);
            $newUser->setState(User::Activated);
            $password = $encoder->encodePassword('mobile', $newUser->getSalt());
            $newUser->setPassword($password);
            $newUser->setHashId(hash('sha512', $userPin . '{' . $newUser->getSalt() . '}'));
            $newUser->addUserRoles($this->getReference('ROLE_USER_CONTENT_MANAGER'));
            $newUser->setState(User::Activated);
            $newUser->setOrganisation($organisation);
            $manager->persist($newUser);
        }
        $manager->flush();

        $userPin = 'system-uploader';
        $newUser = new User();
        $newUser->setUsername($userPin);
        $newUser->setState(User::Activated);
        $password = $encoder->encodePassword('mobile', $newUser->getSalt());
        $newUser->setPassword($password);
        $newUser->setHashId(hash('sha512', $userPin . '{' . $newUser->getSalt() . '}'));
        $newUser->addUserRoles($this->getReference('ROLE_USER_CONTENT_MANAGER'));
        $newUser->addUserRoles($this->getReference('ROLE_USER_MOBILE'));
        $newUser->setState(User::Activated);
        $newUser->setOrganisation($organisation);
        $manager->persist($newUser);
        $manager->flush();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 5;
    }
}
