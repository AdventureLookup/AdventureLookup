<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MonsterType;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;


class MonsterTypeData implements FixtureInterface
{
    /**
     * Load a standard list of monster types
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        $data = [
            'Aberration', 'Animal', 'Beast', 'Construct', 'Deathless', 'Dragon', 'Elemental', 'Fey', 'Giant',
            'Humanoid', 'Magical Beast', 'Monstrous humanoid', 'Ooze', 'Outsider', 'Planetouched', 'Plant',
            'Shapechanger', 'Undead', 'Vermin', 'Animate', 'Celestial', 'Monstrosity', 'Fiend'
        ];

        foreach ($data as $d) {
            $i = new MonsterType();
            $i->setName($d);

            $em->persist($i);
        }

        $em->flush();
    }
}