<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Adventure;
use AppBundle\Service\MetaTags;
use Leogout\Bundle\SeoBundle\Provider\SeoGeneratorProvider;
use Leogout\Bundle\SeoBundle\Seo\Basic\BasicSeoGenerator;
use PHPUnit\Framework\TestCase;

class MetaTagsTest extends TestCase
{
    public function testFromResource()
    {
        $resource = $this->createMock(Adventure::class);

        $seoGenerator = $this->createMock(BasicSeoGenerator::class);
        $seoGenerator->expects($this->exactly(3))->method('fromResource')->with($this->equalTo($resource));

        $seo = $this->createMock(SeoGeneratorProvider::class);
        $seo->method('get')->willReturn($seoGenerator);
        $seo->expects($this->exactly(3))->method('get')->withConsecutive(
            [$this->equalTo('basic')],
            [$this->equalTo('og')],
            [$this->equalTo('twitter')]
        );

        $metaTags = new MetaTags($seo);
        $metaTags->fromResource($resource);
    }
}
