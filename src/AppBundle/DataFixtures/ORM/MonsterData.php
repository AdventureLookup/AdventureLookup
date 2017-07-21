<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Monster;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;


class MonsterData implements FixtureInterface
{
    /**
     * Load a standard list of monsters
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        $monsters = [];
        $uniques = [];

        foreach ($monsters as $monsterName) {
            $d = new Monster();
            $d->setName($monsterName);

            $em->persist($d);
        }

        foreach ($uniques as $uniqueName) {
            $d = new Monster();
            $d->setName($uniqueName);
            $d->setIsUnique(true);

            $em->persist($d);
        }

        $em->flush();
    }
}