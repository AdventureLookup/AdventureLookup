<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TestUserData implements FixtureInterface
{
    /**
     * Load a list of test users for development purposes
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        // NEVER RUN THIS ON PRODUCTION
        $data = [
            ['username' => 'user', 'email' => 'user@test.com', 'roles' => ['ROLE_USER']],
            ['username' => 'curator', 'email' => 'curator@test.com', 'roles' => ['ROLE_CURATOR']],
            ['username' => 'admin', 'email' => 'admin@test.com', 'roles' => ['ROLE_ADMIN']],
        ];

        foreach ($data as $d) {
            $entity = new User();
            $entity->setUsername($d['username']);
            $entity->setEmail($d['email']);
            $entity->setRoles($d['roles']);
            $entity->setPlainPassword('asdf');
            $entity->setIsActive(true);

            $em->persist($entity);
        }

        $em->flush();
    }
}