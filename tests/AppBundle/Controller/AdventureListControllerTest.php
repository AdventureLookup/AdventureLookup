<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Adventure;
use Tests\Fixtures\AdventureData;
use Tests\WebTestCase;

class AdventureListControllerTest extends WebTestCase
{
    public function testNewAdventureList()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])->getReferenceRepository();
        /** @var Adventure $adventure */
        $adventure = $referenceRepository->getReference('user-1-adventure-1');

        $session = $this->makeSession(true);

        // Make sure lists page is accessible
        $session->visit("/profile/lists");

        $this->assertPath($session, '/profile/lists/');
        $this->assertTrue($session->getPage()->hasContent('You haven\'t created any lists yet.'));

        $session->getPage()->fillField('adventure_list_name', 'bookmarklist1');
        $session->getPage()->findButton('Submit')->click();

        $this->assertTrue($session->getPage()->hasContent('bookmarklist1'));
    }
}