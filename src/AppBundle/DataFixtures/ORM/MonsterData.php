<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Monster;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;


class MonsterData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [MonsterTypeData::class];
    }

    /**
     * Load a standard list of monsters
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        $aberration = $this->getReference('monster-type-aberration');
        $beast = $this->getReference('monster-type-beast');
        $celestial = $this->getReference('monster-type-celestial');
        $construct = $this->getReference('monster-type-construct');
        $dragon = $this->getReference('monster-type-dragon');
        $elemental = $this->getReference('monster-type-elemental');
        $fey = $this->getReference('monster-type-fey');
        $fiend = $this->getReference('monster-type-fiend');
        $giant = $this->getReference('monster-type-giant');
        $humanoid = $this->getReference('monster-type-humanoid');
        $monstrosity = $this->getReference('monster-type-monstrosity');
        $ooze = $this->getReference('monster-type-ooze');
        $plant = $this->getReference('monster-type-plant');
        $undead = $this->getReference('monster-type-undead');

        $monsters = [
            ['name' => 'Orc', 'type' => $humanoid],
            ['name' => 'Kobold', 'type' => $humanoid],
            ['name' => 'Goblin', 'type' => $humanoid],
            ['name' => 'Gnoll', 'type' => $humanoid],
            ['name' => 'Hobgoblin', 'type' => $humanoid],
            ['name' => 'Beholder', 'type' => $aberration],
            ['name' => 'Black Pudding', 'type' => $ooze],
            ['name' => 'Mind Flayer', 'type' => $aberration],
            ['name' => 'Drow', 'type' => $humanoid],
            ['name' => 'Wyrmling', 'type' => $dragon],
            ['name' => 'Bulette', 'type' => $monstrosity],
            ['name' => 'Hill Giant', 'type' => $giant],
            ['name' => 'Stone Giant', 'type' => $giant],
            ['name' => 'Frost Giant', 'type' => $giant],
            ['name' => 'Storm Giant', 'type' => $giant],
            ['name' => 'Kuo-Toa', 'type' => $humanoid],
            ['name' => 'Lich', 'type' => $undead],
            ['name' => 'Slaad', 'type' => $aberration],
            ['name' => 'Umber Hulk', 'type' => $monstrosity],
            ['name' => 'Arcanamite', 'type' => $monstrosity],
            ['name' => 'Warhorse', 'type' => $beast],
            ['name' => 'Gazer', 'type' => $aberration],
            ['name' => 'Skin Bat', 'type' => $undead],
            ['name' => 'Deva', 'type' => $celestial],
            ['name' => 'Unicorn', 'type' => $celestial],
            ['name' => 'Pegasus', 'type' => $celestial],
            ['name' => 'Eye Golem', 'type' => $construct],
            ['name' => 'Homunculus', 'type' => $construct],
            ['name' => 'Flesh Golem', 'type' => $construct],
            ['name' => 'Myr', 'type' => $construct],
            ['name' => 'Stone Golem', 'type' => $construct],
            ['name' => 'Azer', 'type' => $elemental],
            ['name' => 'Djinni', 'type' => $elemental],
            ['name' => 'Gargoyl', 'type' => $elemental],
            ['name' => 'Salamander', 'type' => $elemental],
            ['name' => 'Spark', 'type' => $elemental],
            ['name' => 'Water Elemental', 'type' => $elemental],
            ['name' => 'Annis Hag', 'type' => $fey],
            ['name' => 'Boggle', 'type' => $fey],
            ['name' => 'Cactid', 'type' => $plant],
            ['name' => 'Dragonleaf Tree', 'type' => $plant],
            ['name' => 'Myconid', 'type' => $plant],
            ['name' => 'Vegepygmy', 'type' => $plant],
            ['name' => 'Baneling', 'type' => $fiend],
            ['name' => 'Night Hag', 'type' => $fiend],
            ['name' => 'Hell Hound', 'type' => $fiend],
            ['name' => 'Ink Devil', 'type' => $fiend],
            ['name' => 'Rakshasa', 'type' => $fiend],
            ['name' => 'Coral Drake', 'type' => $dragon],
            ['name' => 'Ash Drake', 'type' => $dragon],
            ['name' => 'Dragon Turtle', 'type' => $dragon],
            ['name' => 'Luck Dragon', 'type' => $dragon],
            ['name' => 'Pseudodragon', 'type' => $dragon],
            ['name' => 'Star Drake', 'type' => $dragon],
        ];
        $uniques = [
            ['name' => 'River King', 'type' => $fey],
            ['name' => 'Sarastra', 'type' => $fey],
            ['name' => 'Kalarel', 'type' => $humanoid],
            ['name' => 'Balor', 'type' => $fiend],
            ['name' => 'Akyishigal', 'type' => $fiend],
            ['name' => 'Alquam', 'type' => $fiend],
            ['name' => 'Zmey', 'type' => $dragon],
        ];

        foreach ($monsters as $monster) {
            $d = new Monster();
            $d->setName($monster['name']);
            $d->addType($monster['type']);

            $em->persist($d);
        }

        foreach ($uniques as $unique) {
            $d = new Monster();
            $d->setName($unique['name']);
            $d->addType($unique['type']);
            $d->setIsUnique(true);

            $em->persist($d);
        }

        $em->flush();
    }
}
