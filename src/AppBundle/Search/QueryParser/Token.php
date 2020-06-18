<?php

declare(strict_types=1);

namespace AppBundle\Search\QueryParser;

abstract class Token
{
    public string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->content;
    }
}
