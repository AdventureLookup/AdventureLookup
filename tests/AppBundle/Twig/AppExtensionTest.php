<?php

declare(strict_types=1);

namespace Tests\Twig;

use AppBundle\Service\AffiliateLinkHandler;
use AppBundle\Twig\AppExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;

class AppExtensionTest extends TestCase
{
    /**
     * @var AppExtension
     */
    private $extension;

    public function setUp(): void
    {
        $this->extension = new AppExtension(new AffiliateLinkHandler([[
            'domains' => ['example.com'],
            'param' => 'aff_id',
            'code' => 'aff_code',
        ]], new FilesystemCache()));
    }

    /**
     * @dataProvider bool2strDataProvider
     */
    public function testBool2Str($boolean, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->extension->bool2str($boolean));
    }

    public function testAddAffiliateCode()
    {
        $this->assertEquals(
            ['https://example.com?aff_id=aff_code', true],
            $this->extension->addAffiliateCode('https://example.com')
        );
    }

    public function bool2strDataProvider()
    {
        return [
            [true, 'Yes'],
            [false, 'No'],
            [null, 'Unknown'],
        ];
    }
}
