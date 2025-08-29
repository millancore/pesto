<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Dom\HTMLDocument;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Dom\NodeCollection;

class Pesto
{
    private HTMLDocument $document;
    private bool $isFullHtmlDocument;

    private const string PHP_OPEN_TAG_PLACEHOLDER = '___PHP_OPEN_TAG___';
    private const string PHP_ECHO_TAG_PLACEHOLDER = '___PHP_ECHO_TAG___';
    private const string PHP_CLOSE_TAG_PLACEHOLDER = '___PHP_CLOSE_TAG___';

    private const string TEMPLATE_WRAPPER_ID = '__pesto-template-wrapper__';

    public function __construct(string $html)
    {
        $this->isFullHtmlDocument = str_contains($html, '<html') || str_contains($html, '<body');

        $html = $this->replacePhpTagsWithPlaceholders($html);

        if (!$this->isFullHtmlDocument) {
            $html = '<div id="'.self::TEMPLATE_WRAPPER_ID.'">'.$html.'</div>';
        }

        $this->document = Document::fromString($html);
    }

    public function find(string $selector): NodeCollection
    {
        $nodes = $this->document->querySelectorAll($selector);

        return new NodeCollection($nodes);
    }

    public function getDocument(): HTMLDocument
    {
        return $this->document;
    }

    public function getInnerXML(string $selector): string
    {
        $element = $this->document->querySelector($selector);

        if ($element === null) {
            return '';
        }

        $innerHtml = '';
        foreach ($element->childNodes as $childNode) {
            $innerHtml .= $this->document->saveXml($childNode);
        }

        return $innerHtml;
    }

    private function replacePhpTagsWithPlaceholders(string $html): string
    {
        return str_replace(
            ['<?php', '<?=', '?>'],
            [self::PHP_OPEN_TAG_PLACEHOLDER, self::PHP_ECHO_TAG_PLACEHOLDER, self::PHP_CLOSE_TAG_PLACEHOLDER],
            $html,
        );
    }

    private function replacePhpTagsBack(string $html): string
    {
        return str_replace(
            [self::PHP_OPEN_TAG_PLACEHOLDER, self::PHP_ECHO_TAG_PLACEHOLDER, self::PHP_CLOSE_TAG_PLACEHOLDER],
            ['<?php', '<?=', '?>'],
            $html,
        );
    }

    public function getCompiledTemplate(): string
    {
        return $this->replacePhpTagsBack($this->getRenderedContent());
    }

    private function getRenderedContent(): string
    {
        if ($this->isFullHtmlDocument) {
            return (string) $this->document->saveXML(null, LIBXML_NOXMLDECL | LIBXML_COMPACT | LIBXML_NOEMPTYTAG);
        }

        return $this->getInnerXML('#'.self::TEMPLATE_WRAPPER_ID);
    }
}
