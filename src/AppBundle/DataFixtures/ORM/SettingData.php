<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Setting;

class SettingData implements FixtureInterface
{
    /**
     * Load a standard list of settings
     */
    public function load(ObjectManager $manager)
    {
        $settings = [
            'Birthright', 'Blackmoor', 'Dark Sun', 'Dragonlance', 'Eberron', 'Forgotten Realms', 'Greyhawk',
            'Kingdoms of Kalamar', 'Lankhmar', 'Mystara', 'Planescape', 'Rokugan', 'Mahasarpa', 'Ravenloft',
            'Spelljammer', 'Other',
        ];

        foreach ($settings as $settingName) {
            $setting = new Setting();
            $setting->setName($settingName);

            $manager->persist($setting);
        }

        $manager->flush();
    }
}
