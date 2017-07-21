<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\NPC;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class NPCsData implements FixtureInterface
{
    /**
     * Load a standard list of environments
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $npc = new NPC();
            $npc->setName($faker->name);

            $manager->persist($npc);
        }

        $manager->flush();
    }
}
