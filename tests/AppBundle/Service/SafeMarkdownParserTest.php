<?php

declare(strict_types=1);

namespace Tests\Service;

use AppBundle\Service\SafeMarkdownParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SafeMarkdownParserTest extends TestCase
{
    /**
     * @dataProvider externalLinkOptionDataProvider
     */
    public function testExternalLinkOption(RequestStack $requestStack, array $internalHosts)
    {
        $parser = new SafeMarkdownParser($requestStack);
        $options = $parser->converter->getEnvironment()->getConfig();
        $this->assertEquals($internalHosts, $options['external_link']['internal_hosts']);
    }

    public function testSecurityOptions()
    {
        $parser = new SafeMarkdownParser(new RequestStack());
        $options = $parser->converter->getEnvironment()->getConfig();
        $this->assertEquals('escape', $options['html_input']);
        $this->assertEquals(false, $options['allow_unsafe_links']);
        $this->assertEquals(20, $options['max_nesting_level']);
    }

    public function testRemovesImages()
    {
        $parser = new SafeMarkdownParser(new RequestStack());
        $result = $parser->convert('Begin ![](https://example.com/img.png) End');
        $this->assertEquals("<p>Begin  End</p>\n", $result);
    }

    public function testWrapsTables()
    {
        $parser = new SafeMarkdownParser(new RequestStack());
        $result = $parser->convert(<<<EOD
        | Column 1 |
        | -------- |
        | Entry 1  |
        EOD);
        $this->assertStringStartsWith('<div class="table-responsive"><table class="table">', $result);
        $this->assertStringEndsWith("</table></div>\n", $result);
    }

    public function testEscapesHTML()
    {
        $parser = new SafeMarkdownParser(new RequestStack());
        $markdown = '<h1>test</h1>';
        $result = $parser->convert($markdown);
        $this->assertEquals(htmlspecialchars($markdown)."\n", $result);
    }

    public function testDisallowsMaliciousURLs()
    {
        $parser = new SafeMarkdownParser(new RequestStack());
        $markdown = '[hello](javascript:void(0))';
        $result = $parser->convert($markdown);
        $this->assertEquals("<p><a>hello</a></p>\n", $result);
    }

    public function externalLinkOptionDataProvider()
    {
        $cases = [];

        $cases[] = [
            new RequestStack(),
            [],
        ];

        // Request without a Host
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $cases[] = [
            $requestStack,
            [],
        ];

        $request = new Request([], [], [], [], [], ['HTTP_HOST' => 'example.com']);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $cases[] = [
            $requestStack,
            ['example.com'],
        ];

        return $cases;
    }
}
