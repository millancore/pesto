<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;
use Dom\ProcessingInstruction;
use Millancore\Pesto\Dom\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    private HTMLDocument $document;
    private Node $pNode;
    private Node $lastNode;

    public function setUp(): void
    {
        $html = '<div><p id="first" class="test">First</p><!-- comment --><span id="second">Second</span><b id="last">Last</b></div>';

        $this->document = HTMLDocument::createFromString(
            $html,
            HTML_NO_DEFAULT_NS | LIBXML_NOERROR,
        );

        $pElement = $this->document->querySelector('p');
        $this->pNode = new Node($pElement);

        $lastElement = $this->document->querySelector('b');
        $this->lastNode = new Node($lastElement);
    }

    public function testGetAttribute(): void
    {
        $this->assertEquals('test', $this->pNode->getAttribute('class'));
        $this->assertNull($this->pNode->getAttribute('non-existent-attribute'));
    }

    public function testHasAttribute(): void
    {
        $this->assertTrue($this->pNode->hasAttribute('id'));
        $this->assertFalse($this->pNode->hasAttribute('non-existent-attribute'));
    }

    public function testGetNextSiblingSkipsNonElementNodes(): void
    {
        $sibling = $this->pNode->getNextSibling();

        $this->assertInstanceOf(Node::class, $sibling);
        $this->assertEquals('second', $sibling->getAttribute('id'));
    }

    public function testGetNextSiblingReturnsNullForLastNode(): void
    {
        $this->assertNull($this->lastNode->getNextSibling());
    }

    public function testRemoveAttribute(): void
    {
        $this->assertTrue($this->pNode->hasAttribute('class'));
        $this->pNode->removeAttribute('class');
        $this->assertFalse($this->pNode->hasAttribute('class'));
    }

    public function testInsertBefore(): void
    {
        $newNode = $this->document->createElement('i');
        $newNode->textContent = 'New';
        $this->pNode->insertBefore($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('<i>New</i><p', $html);
    }

    public function testInsertAfter(): void
    {
        $newNode = $this->document->createElement('i');
        $newNode->textContent = 'Another';
        $this->pNode->insertAfter($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);

        $this->assertStringContainsString('</p><i>Another</i><!-- comment --><span', $html);
    }

    public function testInsertAfterLastNode(): void
    {
        $newNode = $this->document->createElement('u');
        $newNode->textContent = 'The Very End';
        $this->lastNode->insertAfter($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('</b><u>The Very End</u>', $html);
    }

    public function testReplaceWith(): void
    {
        $newNode = $this->document->createElement('h1');
        $newNode->textContent = 'Replaced';
        $this->pNode->replaceWith($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);

        $this->assertStringNotContainsString('<p id="first"', $html);
        $this->assertStringContainsString('<h1>Replaced</h1>', $html);
    }

    public function testCreateProcessingInstruction(): void
    {
        $pi = $this->pNode->createProcessingInstruction('php', 'echo "test";');

        $this->assertInstanceOf(ProcessingInstruction::class, $pi);
        $this->assertEquals('php', $pi->target);
        $this->assertEquals('echo "test";', $pi->data);
    }

    public function testGetOuterXml(): void
    {
        $xml = $this->pNode->getOuterXML();
        $this->assertEquals('<p id="first" class="test">First</p>', $xml);
    }

    public function testCreateDocumentFragment(): void
    {
        $fragment = $this->pNode->createDocumentFragment();
        $this->assertInstanceOf(\Dom\DocumentFragment::class, $fragment);
    }
}
