<?php

namespace Millancore\Pesto\Compiler;

use Dom\HTMLDocument;
use const Dom\HTML_NO_DEFAULT_NS;

class Pesto
{
    private HTMLDocument $document;

    private bool $hasOriginalRootTags;

    private bool $wasWrappedForTemplate = false;

    private const string PHP_OPEN_TAG_PLACEHOLDER = '___PHP_OPEN_TAG___';
    private const string PHP_ECHO_TAG_PLACEHOLDER = '___PHP_ECHO_TAG___';
    private const string PHP_CLOSE_TAG_PLACEHOLDER = '___PHP_CLOSE_TAG___';

    public function __construct(string $html)
    {
        $this->hasOriginalRootTags =
            str_contains($html, '<html') ||
            str_contains($html, '<body');

        $html = $this->replacePhpTags($html);

        $html = $this->wrapTemplateIfNeeded($html);

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

    private function wrapTemplateIfNeeded(string $html): string
    {
        $trimmedHtml = trim($html);

        if (str_starts_with($trimmedHtml, '<template')) {
            $this->wasWrappedForTemplate = true;
            return '<div id="__pesto-template-wrapper__">' . $html . '</div>';
        }

        return $html;
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
        return $this->replacePhpTagsBack($this->getRenderedContent());
    }

    private function getRenderedContent(): string
    {
        if ($this->hasOriginalRootTags) {
            return $this->document->saveXml(null, LIBXML_NOXMLDECL | LIBXML_COMPACT);
        }

        if ($this->wasWrappedForTemplate) {
            return $this->getInnerXML('#__pesto-template-wrapper__');
        }

        return $this->getInnerXML('body');
    }




}