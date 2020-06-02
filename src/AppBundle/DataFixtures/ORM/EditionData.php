<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Edition;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class EditionData implements FixtureInterface
{
    const SORT_GAP = 10;

    /**
     * Load a standard list of editions
     */
    public function load(ObjectManager $manager)
    {
        $editions = [
            'OD&D',
            'AD&D',
            'BECMI. AD&D 2',
            '3rd Edition',
            '3.5',
            'Pathfinder',
            '4th Edition',
            '4th Essentials',
            '5th Edition',
            'OSR',
            'DCC',
        ];

        $i = self::SORT_GAP;
        foreach ($editions as $editionName) {
            $edition = new Edition();
            $edition->setName($editionName);
            $edition->setPosition($i);

            $manager->persist($edition);
            $i += self::SORT_GAP;
        }

        $manager->flush();
    }
}
