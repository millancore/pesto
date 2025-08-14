<?php

namespace Millancore\Pesto\Dom;

use Dom\HTMLDocument;
use const Dom\HTML_NO_DEFAULT_NS;

class Document
{
    public static function fromString(string $source) : HTMLDocument
    {
        return HTMLDocument::createFromString(
            $source,
            HTML_NO_DEFAULT_NS | LIBXML_NOERROR
        );
    }

}