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
    /** @var array<int, string> */
    private array $phpBlocks = [];

    private const string TEMPLATE_WRAPPER_ID = '__pesto-template-wrapper__';
    private const string PHP_BLOCK_PLACEHOLDER_PREFIX = '<!--__PESTO_PHP_BLOCK_';

    public function __construct(string $html)
    {
        $this->isFullHtmlDocument = str_contains($html, '<html') || str_contains($html, '<body');

        $html = $this->extractPhpBlocks($html);

        if (!$this->isFullHtmlDocument) {
            $html = '<div id="'.self::TEMPLATE_WRAPPER_ID.'">'.$html.'</div>';
        }

        $this->document = Document::fromString($html);
    }

    private function extractPhpBlocks(string $html): string
    {
        return preg_replace_callback('/(<\?php|<\?=).*?\?>/s', function ($match) {
            $placeholder = self::PHP_BLOCK_PLACEHOLDER_PREFIX.count($this->phpBlocks).'__-->';
            $this->phpBlocks[] = $match[0];

            return $placeholder;
        }, $html);
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

    public function getCompiledTemplate(): string
    {
        $content = $this->getRenderedContent();

        $placeholders = [];
        foreach ($this->phpBlocks as $index => $phpBlock) {
            $placeholders[] = self::PHP_BLOCK_PLACEHOLDER_PREFIX.$index.'__-->';
        }

        return str_replace($placeholders, $this->phpBlocks, $content);
    }

    private function getRenderedContent(): string
    {
        if ($this->isFullHtmlDocument) {
            return (string) $this->document->saveXML(null, LIBXML_NOXMLDECL | LIBXML_COMPACT | LIBXML_NOEMPTYTAG);
        }

        return $this->getInnerXML('#'.self::TEMPLATE_WRAPPER_ID);
    }
}
