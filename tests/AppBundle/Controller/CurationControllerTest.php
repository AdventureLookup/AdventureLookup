<?php

namespace Tests\AppBundle\Controller;

use Tests\Fixtures\CuratedDomainUserData;
use Tests\Fixtures\CurationData;
use Tests\WebTestCase;

class CurationControllerTest extends WebTestCase
{
    public function testLinkList()
    {
        $this->loadFixtures([CurationData::class, CuratedDomainUserData::class]);
        $session = $this->makeSession([
            'username' => 'curator',
            'password' => 'curator',
        ]);
        $session->visit('/curation/links');
        $this->assertTrue($session->getPage()->hasContent('Adventure 1'));
        $this->assertTrue($session->getPage()->hasContent('Adventure 2'));
        $this->assertTrue($session->getPage()->hasContent('Adventure 3'));
        $this->assertFalse($session->getPage()->hasContent('Adventure 4'));

        $this->assertTrue($session->getPage()->hasContent('https://example.com/1.pdf'));
        $this->assertTrue($session->getPage()->hasContent('https://example.com/2.pdf'));
        $this->assertTrue($session->getPage()->hasContent('https://test.example.com/3.pdf'));
        $this->assertFalse($session->getPage()->hasContent('https://example.com/4.pdf'));
    }

    public function testThumbnailList()
    {
        $this->loadFixtures([CurationData::class, CuratedDomainUserData::class]);
        $session = $this->makeSession([
            'username' => 'curator',
            'password' => 'curator',
        ]);
        $session->visit('/curation/image-urls');
        $this->assertTrue($session->getPage()->hasContent('Adventure 1'));
        $this->assertTrue($session->getPage()->hasContent('Adventure 2'));
        $this->assertTrue($session->getPage()->hasContent('Adventure 3'));
        $this->assertFalse($session->getPage()->hasContent('Adventure 4'));

        $this->assertTrue($session->getPage()->hasContent('https://example.com/1.png'));
        $this->assertTrue($session->getPage()->hasContent('https://example.com/2.png'));
        $this->assertTrue($session->getPage()->hasContent('https://test.example.com/3.png'));
        $this->assertFalse($session->getPage()->hasContent('https://example.com/4.png'));
    }
}
