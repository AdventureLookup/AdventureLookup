<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Adventure;
use Tests\Fixtures\AdventureData;
use Tests\WebTestCase;

class AdventureControllerTest extends WebTestCase
{
    const LOGIN_URL = '/login';

    public function testInvalidFilters()
    {
        $this->loadFixtures([AdventureData::class]);

        $session = $this->makeSession();
        $session->visit('/adventures/?filters=not-an-array');
        $this->assertTrue($session->getPage()->hasContent('A community for lazy dungeon masters'));
    }

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

    public function testNonuserActions()
    {
        $referenceRepository = $this->loadFixtures([AdventureData::class])->getReferenceRepository();
        /** @var Adventure $adventure */
        $adventure = $referenceRepository->getReference('user-1-adventure-1');

        $session = $this->makeSession(false);

        // Make sure non authenticated user is redirected to login when clicking new adventure button
        $session->visit('/adventures/');
        $session->getPage()->findLink('Add a new adventure')->click();

        $this->assertPath($session, self::LOGIN_URL);
        $this->assertTrue($session->getPage()->hasContent('You must login to use this feature.'));

        // When clicking make edits as a non user, you should be redirected to login
        $session->visit("/adventures/{$adventure->getSlug()}");
        $session->getPage()->findButton('Suggest Edits')->click();

        $this->assertPath($session, self::LOGIN_URL);
        $this->assertTrue($session->getPage()->hasContent('You must login to use this feature.'));

        // When clicking bookmark show no lists for a nonuser
        $session->visit("/adventures/{$adventure->getSlug()}");
        $session->getPage()->findLink('adventure_list-bookmark-menu-btn')->click();

        $this->assertTrue($session->getPage()->hasContent('Please create a list first, using the link below'));

        // Attempting to navigate to the lists will redirect to login
        $session->visit('/profile/lists/');

        $this->assertPath($session, self::LOGIN_URL);
        $this->assertTrue($session->getPage()->hasContent('You must login to use this feature.'));
    }
}
