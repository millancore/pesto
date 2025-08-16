<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Dom;

use Dom\HTMLDocument;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    public function testGetHtmlDocumentFromString(): void
    {
        $html = '<div>Content</div>';

        $document = Document::fromString($html);

        $this->assertInstanceOf(HTMLDocument::class, $document);
    }
}
