<?php

namespace Tests\AppBundle;

use AppBundle\AppBundle;
use PHPUnit\Framework\TestCase;

class AppBundleTest extends TestCase
{
    public function testTruncate()
    {
        $str = '🔥🔥🔥';
        $this->assertEquals('🔥🔥🔥', AppBundle::truncate($str, 3));

        $str = '🔥🔥🔥';
        $this->assertEquals('🔥🔥…', AppBundle::truncate($str, 2));

        $str = '🔥🔥🔥 '; // ends with whitespace, which should be trimmed
        $this->assertEquals('🔥🔥…', AppBundle::truncate($str, 2));
    }
}
