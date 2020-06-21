<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\TimeProvider;
use PHPUnit\Framework\TestCase;

class TimeProviderTest extends TestCase
{
    public function testMillis()
    {
        $timeProvider = new TimeProvider();

        $now = time() * 1000;
        $result = $timeProvider->millis();
        $this->assertTrue(is_int($result));
        $this->assertGreaterThan($now - 1000, $result);
        $this->assertLessThan($now + 1000, $result);
    }

    public function testYearAndWeek()
    {
        $timeProvider = new TimeProvider();
        $result = $timeProvider->yearAndWeek();
        $this->assertTrue(1 === preg_match('#^\d{4}-\d{1,2}$#', $result));
    }
}
