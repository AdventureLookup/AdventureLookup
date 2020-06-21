<?php

declare(strict_types=1);

namespace Tests\Twig;

use AppBundle\Service\AffiliateLinkHandler;
use AppBundle\Entity\User;
use AppBundle\Twig\AppExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class AppExtensionTest extends TestCase
{
    /**
     * @var AppExtension
     */
    private $extension;

    public function setUp(): void
    {
        $affiliateLinkHandler = new AffiliateLinkHandler([[
            'domains' => ['example.com'],
            'param' => 'aff_id',
            'code' => 'aff_code',
        ]], new FilesystemCache());
        $roleHierarchy = new RoleHierarchy([
            'ROLE_ADMIN' => ['ROLE_USER'],
        ]);
        $this->extension = new AppExtension($affiliateLinkHandler, $roleHierarchy);
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

    public function testFormatRoles()
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);
        $this->assertEquals('User', $this->extension->formatRoles($user));

        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $this->assertEquals('Admin, User', $this->extension->formatRoles($user));
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
