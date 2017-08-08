<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Adventure;
use Tests\Fixtures\AdventureData;
use Tests\WebTestCase;

class AdventureControllerTest extends WebTestCase
{
    public function testDelete()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])->getReferenceRepository();
        /** @var Adventure $adventure */
        $adventure = $referenceRepository->getReference('user-1-adventure-1');

        $session = $this->makeSession(true);

        // Make sure adventure is part of the index
        $session->visit('/adventures/');
        $this->assertTrue($session->getPage()->hasContent($adventure->getTitle()));

        $session->visit("/adventures/{$adventure->getSlug()}");
        $session->getPage()->findButton('Delete')->click();
        $this->assertPath($session, '/adventures/');

        // Verify index still working
        $this->assertWorkingIndex($session);
        // Make sure adventure isn't part of the index any more
        $this->assertFalse($session->getPage()->hasContent($adventure->getTitle()));
    }
}
