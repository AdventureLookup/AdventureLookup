<?php

namespace Tests\AppBundle\Controller;

use Tests\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $this->loadFixtures([]);

        $session = $this->makeSession();

        $session->visit('/');

        $this->assertPath($session, '/adventures');
    }
}
