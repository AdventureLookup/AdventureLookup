<?php

namespace AppBundle\Twig;

use AppBundle\Service\SafeMarkdownParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    private SafeMarkdownParser $safeMarkdownParser;

    public function __construct(SafeMarkdownParser $safeMarkdownParser)
    {
        $this->safeMarkdownParser = $safeMarkdownParser;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('user_provided_markdown_to_safe_html', [$this, 'userMarkdownToHTML'], ['is_safe' => ['all']]),
        ];
    }

    public function userMarkdownToHTML($markdown)
    {
        return $this->safeMarkdownParser->convert($markdown);
    }
}
