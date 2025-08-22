<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Dom;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;
use Dom\ProcessingInstruction;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    private HTMLDocument $document;
    private Node $elementNode;
    private Node $lastNode;

    protected function setUp(): void
    {
        $html = '<div><p id="first" class="test">First</p><!-- comment --><span id="second">Second</span><b id="last">Last</b></div>';

        $this->document = HTMLDocument::createFromString(
            $html,
            HTML_NO_DEFAULT_NS | LIBXML_NOERROR,
        );

        $pElement = $this->document->querySelector('p');
        $this->elementNode = new Node($pElement);

        $lastElement = $this->document->querySelector('b');
        $this->lastNode = new Node($lastElement);
    }

    public function testGetAttributeReturnsValueWhenExists(): void
    {
        $this->assertEquals('test', $this->elementNode->getAttribute('class'));
        $this->assertEquals('first', $this->elementNode->getAttribute('id'));
    }

    public function testGetAttributeReturnsNullWhenNotExists(): void
    {
        $this->assertNull($this->elementNode->getAttribute('non-existent-attribute'));
    }

    public function testSetAttributeAddsNewAttribute(): void
    {
        $this->elementNode->setAttribute('data-test', 'value');

        $this->assertEquals('value', $this->elementNode->getAttribute('data-test'));
    }

    public function testSetAttributeUpdatesExistingAttribute(): void
    {
        $this->elementNode->setAttribute('class', 'updated');

        $this->assertEquals('updated', $this->elementNode->getAttribute('class'));
    }

    public function testHasAttributeReturnsTrueWhenExists(): void
    {
        $this->assertTrue($this->elementNode->hasAttribute('id'));
        $this->assertTrue($this->elementNode->hasAttribute('class'));
    }

    public function testHasAttributeReturnsFalseWhenNotExists(): void
    {
        $this->assertFalse($this->elementNode->hasAttribute('non-existent-attribute'));
    }

    public function testGetNextSiblingReturnsNextElementNode(): void
    {
        $sibling = $this->elementNode->getNextSibling();

        $this->assertInstanceOf(Node::class, $sibling);
        $this->assertEquals('second', $sibling->getAttribute('id'));
    }

    public function testGetNextSiblingReturnsNullForLastNode(): void
    {
        $this->assertNull($this->lastNode->getNextSibling());
    }

    public function testRemoveAttributeRemovesExistingAttribute(): void
    {
        $this->assertTrue($this->elementNode->hasAttribute('class'));

        $this->elementNode->removeAttribute('class');

        $this->assertFalse($this->elementNode->hasAttribute('class'));
    }

    public function testInsertBeforeAddsNodeBeforeCurrent(): void
    {
        $newNode = $this->document->createElement('i');
        $newNode->textContent = 'New';

        $this->elementNode->insertBefore($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('<i>New</i><p', $html);
    }

    public function testInsertAfterAddsNodeAfterCurrent(): void
    {
        $newNode = $this->document->createElement('i');
        $newNode->textContent = 'Another';

        $this->elementNode->insertAfter($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('</p><i>Another</i><!-- comment --><span', $html);
    }

    public function testInsertAfterAppendsWhenNoNextSibling(): void
    {
        $newNode = $this->document->createElement('u');
        $newNode->textContent = 'The Very End';

        $this->lastNode->insertAfter($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('</b><u>The Very End</u>', $html);
    }

    public function testReplaceWithReplacesCurrentNode(): void
    {
        $newNode = $this->document->createElement('h1');
        $newNode->textContent = 'Replaced';

        $this->elementNode->replaceWith($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringNotContainsString('<p id="first"', $html);
        $this->assertStringContainsString('<h1>Replaced</h1>', $html);
    }

    public function testCreateProcessingInstructionReturnsProcessingInstruction(): void
    {
        $pi = $this->elementNode->createProcessingInstruction('xml', 'version="1.0"');

        $this->assertInstanceOf(ProcessingInstruction::class, $pi);
        $this->assertEquals('xml', $pi->target);
        $this->assertEquals('version="1.0"', $pi->data);
    }

    public function testCreatePhpInstructionReturnsPhpProcessingInstruction(): void
    {
        $pi = $this->elementNode->createPHPInstruction('echo "test";');

        $this->assertInstanceOf(ProcessingInstruction::class, $pi);
        $this->assertEquals('php', $pi->target);
        $this->assertEquals('echo "test";', $pi->data);
    }


    public function testCreateDocumentFragmentReturnsDocumentFragment(): void
    {
        $fragment = $this->elementNode->createDocumentFragment();

        $this->assertInstanceOf(\Dom\DocumentFragment::class, $fragment);
    }

    public function testGetDomNodeReturnsUnderlyingDomNode(): void
    {
        $domNode = $this->elementNode->getDomNode();

        $this->assertInstanceOf(\Dom\Element::class, $domNode);
        $this->assertEquals('first', $domNode->getAttribute('id'));
    }

    public function testUnwrapMovesChildrenAndRemovesNode(): void
    {
        // Create a wrapper div with children
        $wrapper = $this->document->createElement('div');
        $wrapper->setAttribute('class', 'wrapper');

        $child1 = $this->document->createElement('span');
        $child1->textContent = 'Child 1';
        $child2 = $this->document->createElement('span');
        $child2->textContent = 'Child 2';

        $wrapper->appendChild($child1);
        $wrapper->appendChild($child2);

        $container = $this->document->querySelector('div');
        $container->appendChild($wrapper);

        $wrapperNode = new Node($wrapper);
        $wrapperNode->unwrap();

        $html = $this->document->saveHtml($container);
        $this->assertStringNotContainsString('<div class="wrapper">', $html);
        $this->assertStringContainsString('<span>Child 1</span>', $html);
        $this->assertStringContainsString('<span>Child 2</span>', $html);
    }

    public function testAfterAddsNodeAfterCurrent(): void
    {
        $newNode = $this->document->createElement('em');
        $newNode->textContent = 'After';

        $this->elementNode->after($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('</p><em>After</em><!-- comment --><span', $html);
    }

    public function testAfterAppendsWhenNoNextSibling(): void
    {
        $newNode = $this->document->createElement('strong');
        $newNode->textContent = 'At End';

        $this->lastNode->after($newNode);

        $div = $this->document->querySelector('div');
        $html = $this->document->saveHtml($div);
        $this->assertStringContainsString('</b><strong>At End</strong>', $html);
    }
}
