<?php

namespace AppBundle\Service;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\Image;
use Symfony\Component\HttpFoundation\RequestStack;

class SafeMarkdownParser
{
    public CommonMarkConverter $converter;

    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getMasterRequest();
        $internalHosts = [];
        if ($request && !empty($request->getHost())) {
            $internalHosts = [$request->getHost()];
        }

        $options = [
             // https://commonmark.thephpleague.com/1.5/security
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'external_link' => [
                // https://commonmark.thephpleague.com/1.5/extensions/external-links/
                'internal_hosts' => $internalHosts,
            ],
        ];

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new ExternalLinkExtension());

        // Delete all images
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) {
            $document = $event->getDocument();
            $walker = $document->walker();
            while ($event = $walker->next()) {
                $node = $event->getNode();
                if (!$event->isEntering() && $node instanceof Image) {
                    $node->detach();
                }
            }
        });

        // Wrap tables in <div class="table-responsive"> and adds the "table" class to <table>.
        // We give this renderer a high priority so that it runs before the built-in TableRenderer.
        $environment->addBlockRenderer(Table::class, new class() implements BlockRendererInterface {
            private TableRenderer $tableRenderer;

            public function __construct()
            {
                $this->tableRenderer = new TableRenderer();
            }

            public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false)
            {
                $block->data['attributes']['class'] = 'table';
                $table = $this->tableRenderer->render($block, $htmlRenderer, $inTightList);
                if (null === $table) {
                    return null;
                }

                return new HtmlElement('div', ['class' => 'table-responsive'], $table);
            }
        }, 999999);

        $this->converter = new CommonMarkConverter($options, $environment);
    }

    public function convert(string $body): string
    {
        return $this->converter->convertToHtml($body);
    }
}
