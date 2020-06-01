<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Author;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AuthorData implements FixtureInterface
{
    /**
     * Load a standard list of authors
     */
    public function load(ObjectManager $manager)
    {
        $authors = [
            'Ed Greenwood',
            'Christian Flach',
            'Matt Colville',
        ];

        foreach ($authors as $authorName) {
            $author = new Author();
            $author->setName($authorName);

            $manager->persist($author);
        }

        $manager->flush();
    }
}
