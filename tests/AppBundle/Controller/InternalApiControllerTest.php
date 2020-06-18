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

    public function testFindSimilarAdventuresAction()
    {
        $this->loadFixtures([SimilarTitlesData::class]);
        $session = $this->makeSession();
        $json = $this->jsonRequest($session, '/autocomplete/similar-adventures?id=1&fieldName=title/description');
        $this->assertCount(1, $json['adventures']);
        $this->assertEquals(self::ADVENTURE_NATURE_ANIMAL['id'], $json['adventures'][0]['id']);
        $this->assertIsFloat($json['adventures'][0]['score']);

        $this->assertCount(1, $json['terms']);
        $this->assertEquals('natur' /* stemmed */, $json['terms'][0]['term']);
        $this->assertIsFloat($json['terms'][0]['tf-idf']);

        $json = $this->jsonRequest($session, '/autocomplete/similar-adventures?id=1&fieldName=blah');
        $this->assertCount(0, $json['adventures']);
        $this->assertCount(0, $json['terms']);

        // TODO: This test could be a lot better:
        // - test that terms are sorted by TF-IDF
        // - test that at most 20 terms are returned
        // - test other $fieldName|s
        // - test that ID conversion between MySQL and ElasticSearch works correctly.
    }
}
