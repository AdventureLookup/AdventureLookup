<?php

namespace Tests\AppBundle\Controller;

use Tests\Fixtures\CuratedDomainUserData;
use Tests\WebTestCase;

class CuratedDomainControllerTest extends WebTestCase
{
    public function testAll()
    {
        $this->loadFixtures([CuratedDomainUserData::class]);
        $session = $this->makeSession([
            'username' => 'curator',
            'password' => 'curator',
        ]);
        $session->visit('/curation/domains');
        $this->assertTrue($session->getPage()->hasContent('No entries found.'));

        $session->getPage()->clickLink('Create new entry');
        $this->assertPath($session, '/curation/domains/new');

        $session->getPage()->fillField('curated_domain_domain', 'example.com');
        $session->getPage()->fillField('curated_domain_reason', 'I do not like examples.');
        $session->getPage()->pressButton('Save');
        $this->assertPath($session, '/curation/domains');

        $row = $session->getPage()->find('css', 'table tbody tr');
        $this->assertStringContainsString('example.com', $row->getText());
        $this->assertStringContainsString('Blocked', $row->getText());
        $this->assertStringContainsString('by @curator', $row->getText());
        $this->assertStringContainsString('I do not like examples.', $row->getText());
        $this->assertStringContainsString('Edit', $row->getText());
        $this->assertStringContainsString('Delete', $row->getText());

        $row->clickLink('Edit');
        $this->assertPath($session, '/curation/domains/1/edit');
        $session->getPage()->findField('curated_domain_type_1')->setValue('V');
        $session->getPage()->pressButton('Update');
        $this->assertPath($session, '/curation/domains');

        $row = $session->getPage()->find('css', 'table tbody tr');
        $this->assertStringContainsString('example.com', $row->getText());
        $this->assertStringContainsString('Verified', $row->getText());
        $this->assertStringContainsString('by @curator', $row->getText());
        $this->assertStringContainsString('I do not like examples.', $row->getText());
        $this->assertStringContainsString('Edit', $row->getText());
        $this->assertStringContainsString('Delete', $row->getText());

        $row->pressButton('Delete');
        $this->assertPath($session, '/curation/domains');
        $this->assertTrue($session->getPage()->hasContent('No entries found.'));
    }
}
