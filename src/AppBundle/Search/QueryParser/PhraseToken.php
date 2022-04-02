<?php

declare(strict_types=1);

namespace AppBundle\Search\QueryParser;

class PhraseToken extends StringToken
{
    protected const TOKEN_KIND = 'phrase';
}
