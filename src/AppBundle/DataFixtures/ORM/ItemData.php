<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Item;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ItemData implements FixtureInterface
{
    /**
     * Load a standard list of environments
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $items = [
            'Arcane Door',
            'Arcane Lock Box',
            'Arcanum Spellbook',
            'Archer Gloves',
            'Armbands of Prestidigitation',
            'Auril\'s Kiss',
            'Axe of Changing State',
            'Azura\'s Star',
            'Bag of Bags',
            'Banished One\'s Cloak',
            'Beholder Eye',
            'Belt of Battle',
            'Blanket of Warmness',
            'Book of Time',
        ];

        foreach ($items as $itemName) {
            $item = new Item();
            $item->setName($itemName);

            $manager->persist($item);
        }

        $manager->flush();
    }
}
