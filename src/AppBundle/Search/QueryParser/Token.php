<?php

declare(strict_types=1);

namespace AppBundle\Search\QueryParser;

abstract class Token implements \JsonSerializable
{
    public string $content;

    protected const TOKEN_KIND = '';

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'token',
            'kind' => static::TOKEN_KIND,
            'content' => $this->content,
        ];
    }
}
