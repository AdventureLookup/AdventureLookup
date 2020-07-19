<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public static function truncate(string $value, int $length): string
    {
        if (mb_strlen($value) > $length) {
            return rtrim(mb_substr($value, 0, $length)).'…';
        }

        return $value;
    }
}
