<?php


namespace Tests\Fixtures;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setUsername("User #{$i}");
            $user->setEmail("user{$i}@example.com");
            $user->setRoles(['ROLE_USER']);
            $user->setPlainPassword("user{$i}");
            $user->setIsActive(true);

            $em->persist($user);
            $this->addReference("user-{$i}", $user);
        }

        $em->flush();
    }
}
