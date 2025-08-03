<?php

namespace Millancore\Pesto\Compiler;

use Dom\HTMLDocument;
use const Dom\HTML_NO_DEFAULT_NS;

class Pesto
{
    private HTMLDocument $document;

    public function __construct(string $html)
    {
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
       return $this->document->saveXml();
    }


}