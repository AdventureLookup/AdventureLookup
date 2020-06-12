<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\AffiliateLinkHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;

class AffiliateLinkHandlerTest extends TestCase
{
    /**
     * @var AffiliateLinkHandler
     */
    private $affiliateLinkHandler;

    public function setUp(): void
    {
        $affiliateMappings = [
            [
                'domains' => ['example.com', 'example.org'],
                'param' => 'aff_id',
                'code' => 'aff_code',
            ],
            [
                'domains' => ['foo.bar'],
                'param' => 'aff_id2',
                'code' => 'aff_code2',
            ],
            [
                // add duplicate domain example.com to verify that testGetDomains calls array_unique
                'domains' => ['example.com'],
                'param' => 'foo',
                'code' => 'bar',
            ],
        ];
        $this->affiliateLinkHandler = new AffiliateLinkHandler($affiliateMappings, new FilesystemCache());
    }

    public function testGetDomains()
    {
        $this->assertEquals(['example.com', 'example.org', 'foo.bar'], $this->affiliateLinkHandler->getDomains());
    }

    /**
     * @dataProvider urlDataProvider
     */
    public function testAddAffiliateCode(string $inputUrl = null, string $expectedOutputUrl = null, bool $affiliateCodeAdded)
    {
        $this->assertEquals(
            [$expectedOutputUrl, $affiliateCodeAdded],
            $this->affiliateLinkHandler->addAffiliateCode($inputUrl)
        );
    }

    public function urlDataProvider()
    {
        $unrelatedUrl = 'https://www.123.co.uk/foo/bar/../baz/test 123?a=abc&b= aaa&id=1000#foo-bar&x=100';

        return [
            [null, null, false],
            ['', '', false],
            [$unrelatedUrl, $unrelatedUrl, false],
            ['http://example.com/foo/bar/../baz/test 123?a=abc&b= aaa&id=1000#foo-bar&x=100', 'http://example.com/foo/bar/../baz/test 123?a=abc&b= aaa&id=1000&aff_id=aff_code#foo-bar&x=100', true],
            ['http://example.org/?aff_id=test', 'http://example.org/?aff_id=aff_code', true],
            ['http://www.foo.bar/?aff_id=test&aff_id2=test2', 'http://www.foo.bar/?aff_id=test&aff_id2=aff_code2', true],
        ];
    }
}
