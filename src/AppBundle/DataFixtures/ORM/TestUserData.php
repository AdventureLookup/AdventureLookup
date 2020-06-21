<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TestUserData extends Fixture
{
    /**
     * Load a list of test users for development purposes
     */
    public function load(ObjectManager $em)
    {
        if (!in_array($this->container->getParameter('kernel.environment'), ['dev', 'test', 'heroku'], true)) {
            throw new \Exception('You can only load test user data when Symfony is running in a dev or test environment.');
        }

        $data = [
            ['username' => 'user', 'email' => 'user@example.com', 'roles' => ['ROLE_USER']],
            ['username' => 'curator', 'email' => 'curator@example.com', 'roles' => ['ROLE_CURATOR']],
            ['username' => 'admin', 'email' => 'admin@example.com', 'roles' => ['ROLE_ADMIN']],
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
