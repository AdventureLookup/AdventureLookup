<?php

namespace Tests\Fixtures;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class CuratedDomainUserData extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $user = new User();
        $user->setUsername('curator');
        $user->setEmail('curator@example.com');
        $user->setRoles(['ROLE_CURATOR']);
        $user->setPlainPassword('curator');
        $user->setIsActive(true);

        $em->persist($user);
        $this->addReference('user', $user);

        $em->flush();
    }
}
