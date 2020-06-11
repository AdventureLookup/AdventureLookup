<?php

namespace Tests\AppBundle\DataFixtures;

use AppBundle\DataFixtures\ORM\RandomAdventureData;
use AppBundle\DataFixtures\ORM\TestUserData;
use Tests\WebTestCase;

/**
 * Test whether the fixtures can be loaded.
 * Note: This will not generate code coverage, because the LiipFunctionalTestBundle
 * is smart and caches the databases created by loading the fixtures in previous
 * tests. This might be improvable once they release version 2 of the bundle,
 * since they refactored fixture loading into multiple services.
 */
class TestFixturesLoadable extends WebTestCase
{
    public function testLoadRandomAdventures()
    {
        $this->loadFixtures([RandomAdventureData::class]);
    }

    public function testLoadTestUsers()
    {
        $this->loadFixtures([TestUserData::class]);
    }
}
