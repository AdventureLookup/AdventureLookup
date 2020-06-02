<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Publisher;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PublisherData implements FixtureInterface
{
    /**
     * Load a standard list of environments
     */
    public function load(ObjectManager $manager)
    {
        $publishers = [
            'TSR',
            'WotC',
            'Paizo',
            'Goodman Games',
            'Necromancer Games',
            "Judge's Guild",
        ];

        foreach ($publishers as $publisherName) {
            $publisher = new Publisher();
            $publisher->setName($publisherName);

            $manager->persist($publisher);
        }

        $manager->flush();
    }
}
