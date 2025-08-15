<?php

declare(strict_types=1);

namespace Millancore\Pesto\Dom;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;

class Document
{
    public static function fromString(string $source): HTMLDocument
    {
        return HTMLDocument::createFromString(
            $source,
            HTML_NO_DEFAULT_NS | LIBXML_NOERROR,
        );
    }
}
