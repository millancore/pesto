<?php

namespace Millancore\Pesto\Compiler;

use Dom\HTMLDocument;
use const Dom\HTML_NO_DEFAULT_NS;

class Pesto
{
    private HTMLDocument $document;

    private bool $hasOriginalRootTags;

    private const string PHP_OPEN_TAG_PLACEHOLDER = '___PHP_OPEN_TAG___';
    private const string PHP_ECHO_TAG_PLACEHOLDER = '___PHP_ECHO_TAG___';
    private const string PHP_CLOSE_TAG_PLACEHOLDER = '___PHP_CLOSE_TAG___';

    public function __construct(string $html)
    {
        $this->hasOriginalRootTags =
            str_contains($html, '<html') ||
            str_contains($html, '<body');

        $html = $this->replacePhpTags($html);

        $this->document = HTMLDocument::createFromString(
            $html,
            HTML_NO_DEFAULT_NS | LIBXML_NOERROR
        );
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

    private function replacePhpTags(string $html): string
    {
        return str_replace(
            ['<?php', '<?=', '?>'],
            [
                self::PHP_OPEN_TAG_PLACEHOLDER,
                self::PHP_ECHO_TAG_PLACEHOLDER,
                self::PHP_CLOSE_TAG_PLACEHOLDER
            ],
            $html
        );
    }

    private function replacePhpTagsBack(string $html): string
    {
        return str_replace([
            self::PHP_OPEN_TAG_PLACEHOLDER,
            self::PHP_ECHO_TAG_PLACEHOLDER,
            self::PHP_CLOSE_TAG_PLACEHOLDER
        ], ['<?php', '<?=', '?>'],
            $html
        );
    }

    public function getCompiledTemplate(): string
    {
        if (!$this->hasOriginalRootTags) {
            $rendered = $this->getInnerXML('body');
        } else {
            $rendered = $this->document->saveXml(null, LIBXML_NOXMLDECL| LIBXML_COMPACT);
        }

        return $this->replacePhpTagsBack($rendered);
    }


}