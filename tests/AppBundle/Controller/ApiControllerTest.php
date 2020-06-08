<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Adventure;
use Tests\Fixtures\AdventureData;
use Tests\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testIndexAction()
    {
        $this->loadFixtures([AdventureData::class]);

        $session = $this->makeSession();
        $session->visit('/api/adventures');
        $this->assertEquals('application/json', $session->getResponseHeader('Content-Type'));
        $json = json_decode($session->getPage()->getContent());

        $this->assertEquals(AdventureData::NUM_ADVENTURES, $json->total_count);
        $this->assertCount(AdventureData::NUM_ADVENTURES, $json->adventures);

        foreach ($json->adventures as $adventure) {
            $this->stringStartsWith('Adventure #', $adventure->title);
        }
    }

    public function testRedirect()
    {
        $session = $this->makeSession();
        $session->visit('/api/adventures/?foo=bar');
        $this->assertPath($session, '/api/adventures?foo=bar');
    }

    public function testShowAction()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])->getReferenceRepository();
        /** @var Adventure $adventure */
        $adventure = $referenceRepository->getReference('adventure-1');

        $session = $this->makeSession();
        $session->visit("/api/adventures/{$adventure->getId()}");
        $this->assertEquals('application/json', $session->getResponseHeader('Content-Type'));
        $json = json_decode($session->getPage()->getContent());

        $this->assertEquals('Adventure #1', $json->adventure->title);
        $this->assertEquals($adventure->getId(), $json->adventure->id);
        $this->assertEmpty($json->reviews);
        $this->assertEmpty($json->change_requests);
    }

    public function testDocsAction()
    {
        $session = $this->makeSession();
        $session->visit('/api');
        $this->assertTrue($session->getPage()->hasContent('API Docs'));
    }
}
