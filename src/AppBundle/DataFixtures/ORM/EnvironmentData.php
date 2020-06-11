<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Environment;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class EnvironmentData implements FixtureInterface
{
    /**
     * Load a standard list of environments
     */
    public function load(ObjectManager $manager)
    {
        $environments = [
            'Dungeon',
            'Wilderness',
            'Swamp',
            'City',
            'Town',
            'Ship',
            'Underdark',
            'Underwater',
            'Stronghold',
            'Planes',
        ];

        foreach ($environments as $environmentName) {
            $environment = new Environment();
            $environment->setName($environmentName);

            $manager->persist($environment);
        }

        $manager->flush();
    }
}
