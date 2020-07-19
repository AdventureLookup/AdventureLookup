<?php

namespace AppBundle\Service;

use Leogout\Bundle\SeoBundle\Provider\SeoGeneratorProvider;

class MetaTags
{
    private SeoGeneratorProvider $seo;

    public function __construct(SeoGeneratorProvider $seo)
    {
        $this->seo = $seo;
    }

    public function fromResource($resource): void
    {
        $this->seo->get('basic')->fromResource($resource);
        $this->seo->get('og')->fromResource($resource);
        $this->seo->get('twitter')->fromResource($resource);
    }
}
