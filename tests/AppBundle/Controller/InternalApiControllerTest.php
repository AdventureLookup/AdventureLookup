<?php

namespace Tests\AppBundle\Controller;

use Tests\Fixtures\SimilarTitlesData;
use Tests\WebTestCase;

class InternalApiControllerTest extends WebTestCase
{
    const ADVENTURE_NATURE = [
        'id' => 1,
        'title' => 'nature',
        'slug' => 'nature',
    ];
    const ADVENTURE_NATURE_ANIMAL = [
        'id' => 2,
        'title' => 'nature animal',
        'slug' => 'nature-animal',
    ];
    const ADVENTURE_NATURE_TYPO = [
        'id' => 3,
        'title' => 'naturre',
        'slug' => 'naturre',
    ];
    const ADVENTURE_ANIMAL = [
        'id' => 4,
        'title' => 'animal',
        'slug' => 'animal',
    ];

    public function testFindSimilarTitlesAction()
    {
        $this->loadFixtures([SimilarTitlesData::class]);

        $session = $this->makeSession();
        $json = $this->jsonRequest($session, '/autocomplete/similar-titles?q=nature');
        $this->assertCount(3, $json);
        $this->assertEquals(self::ADVENTURE_NATURE, $json[0]);
        $this->assertEquals(self::ADVENTURE_NATURE_TYPO, $json[1]);
        $this->assertEquals(self::ADVENTURE_NATURE_ANIMAL, $json[2]);

        $json = $this->jsonRequest($session, '/autocomplete/similar-titles?q=animal');
        $this->assertCount(2, $json);
        $this->assertEquals(self::ADVENTURE_ANIMAL, $json[0]);
        $this->assertEquals(self::ADVENTURE_NATURE_ANIMAL, $json[1]);

        $json = $this->jsonRequest($session, '/autocomplete/similar-titles?q=');
        $this->assertCount(0, $json);

        $json = $this->jsonRequest($session, '/autocomplete/similar-titles?q=animal&ignoreId=4');
        $this->assertCount(1, $json);
        $this->assertEquals(self::ADVENTURE_NATURE_ANIMAL, $json[0]);
    }
}
