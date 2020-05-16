<?php

namespace AppBundle\Service;

class TimeProvider
{
    /**
     * @return int The current time in milliseconds.
     */
    public function millis()
    {
        return (int)round(microtime(true) * 1000);
    }
}