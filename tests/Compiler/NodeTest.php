<?php

namespace Millancore\Pesto\Tests\Compiler;

use DOMDocument;
use DOMProcessingInstruction;
use Millancore\Pesto\Compiler\Node;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    private DOMDocument $dom;
    private Node $pNode;
    private Node $lastNode;

    public function setUp(): void
    {
        $this->dom = new DOMDocument();

        $html = '<div><p id="first" class="test">First</p><!-- comment --><span id="second">Second</span><b id="last">Last</b></div>';

        $this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $pElement = $this->dom->getElementsByTagName('p')->item(0);
        $this->pNode = new Node($pElement);

        $lastElement = $this->dom->getElementsByTagName('b')->item(0);
        $this->lastNode = new Node($lastElement);
    }

    public function test_get_attribute(): void
    {
        $this->assertEquals('test', $this->pNode->getAttribute('class'));
        $this->assertNull($this->pNode->getAttribute('non-existent-attribute'));
    }

    public function test_has_attribute(): void
    {
        $this->assertTrue($this->pNode->hasAttribute('id'));
        $this->assertFalse($this->pNode->hasAttribute('non-existent-attribute'));
    }

    public function test_get_next_sibling(): void
    {
        $sibling = $this->pNode->getNextSibling();

        $this->assertInstanceOf(Node::class, $sibling);
        $this->assertEquals('second', $sibling->getAttribute('id'));
        $this->assertNull($this->lastNode->getNextSibling());
    }

    public function test_remove_attribute(): void
    {
        $this->assertTrue($this->pNode->hasAttribute('class'));
        $this->pNode->removeAttribute('class');
        $this->assertFalse($this->pNode->hasAttribute('class'));
    }

    public function test_insert_before(): void
    {
        $newNode = $this->dom->createElement('i', 'New');
        $this->pNode->insertBefore($newNode);


        $html = $this->dom->getElementsByTagName('div')->item(0)->C14N();
        $this->assertStringContainsString('<i>New</i><p', $html);
    }

    public function test_insert_after(): void
    {
        $newNode = $this->dom->createElement('i', 'Another');
        $this->pNode->insertAfter($newNode);

        $html = $this->dom->getElementsByTagName('div')->item(0)->C14N();
        $this->assertStringContainsString('</p><i>Another</i>', $html);
    }

    public function test_insert_after_last_node(): void
    {
        $newNode = $this->dom->createElement('u', 'The Very End');
        $this->lastNode->insertAfter($newNode);

        $html = $this->dom->getElementsByTagName('div')->item(0)->C14N();
        $this->assertStringContainsString('</b><u>The Very End</u>', $html);
    }

    public function test_create_processing_instruction(): void
    {
        $pi = $this->pNode->createProcessingInstruction('php', 'echo "test";');

        $this->assertInstanceOf(DOMProcessingInstruction::class, $pi);
        $this->assertEquals('php', $pi->target);
        $this->assertEquals('echo "test";', $pi->data);
    }
}