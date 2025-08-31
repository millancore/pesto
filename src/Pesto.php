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
    /** @var array<string, string> */
    private array $phpBlocks = [];
    private ?string $leadingPhpBlock = null;

    private const string TEMPLATE_WRAPPER_ID = '__pesto-template-wrapper__';
    private const string PHP_BLOCK_PLACEHOLDER_PREFIX = '__PESTO_PHP_BLOCK__';

    public function __construct(string $html)
    {
        if (preg_match('/^((?:\s*(?:<\?php|<\?=).*?\?>)+)/s', $html, $matches)) {
            $this->leadingPhpBlock = $matches[1];
            $html = substr($html, strlen($matches[1]));
        }

        $this->isFullHtmlDocument = str_contains($html, '<html') || str_contains($html, '<body');

        $html = $this->extractPhpBlocks($html);

        if (!$this->isFullHtmlDocument && trim($html) !== '') {
            $html = '<div id="'.self::TEMPLATE_WRAPPER_ID.'">'.$html.'</div>';
        }

        $this->document = Document::fromString($html);
    }

    private function extractPhpBlocks(string $html): string
    {
        return preg_replace_callback('/(<\?php|<\?=).*?\?>/s', function ($match) {
            $placeholder = self::PHP_BLOCK_PLACEHOLDER_PREFIX.uniqid('', true);
            $this->phpBlocks[$placeholder] = $match[0];

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

        $content = str_replace(array_keys($this->phpBlocks), array_values($this->phpBlocks), $content);

        return $this->leadingPhpBlock.$content;
    }

    private function getRenderedContent(): string
    {
        if ($this->isFullHtmlDocument) {
            return (string) $this->document->saveXML(null, LIBXML_NOXMLDECL | LIBXML_COMPACT);
        }

        return $this->getInnerXML('#'.self::TEMPLATE_WRAPPER_ID);
    }
}
