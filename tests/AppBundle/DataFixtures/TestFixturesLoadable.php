<?php


namespace Tests\AppBundle\DataFixtures;

use AppBundle\DataFixtures\ORM\RandomAdventureData;
use AppBundle\DataFixtures\ORM\TestUserData;
use Tests\WebTestCase;

class TestFixturesLoadable extends WebTestCase
{
    /**
     * @covers \AppBundle\DataFixtures\ORM\AuthorData
     * @covers \AppBundle\DataFixtures\ORM\EditionData
     * @covers \AppBundle\DataFixtures\ORM\EnvironmentData
     * @covers \AppBundle\DataFixtures\ORM\ItemData
     * @covers \AppBundle\DataFixtures\ORM\MonsterData
     * @covers \AppBundle\DataFixtures\ORM\PublisherData
     * @covers \AppBundle\DataFixtures\ORM\SettingData
     */
    public function testLoadRandomAdventures()
    {
        $this->loadFixtures([RandomAdventureData::class]);
    }

    /**
     * @covers \AppBundle\DataFixtures\ORM\TestUserData
     */
    public function testLoadTestUsers()
    {
        $this->loadFixtures([TestUserData::class]);
    }
}
