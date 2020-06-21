<?php

namespace AppBundle\Service;

class TimeProvider
{
    /**
     * @return int the current time in milliseconds
     */
    public function millis()
    {
        return (int) round(microtime(true) * 1000);
    }

    public function yearAndWeek(): string
    {
        return (new \DateTime('now'))->format('Y-W');
    }
}
