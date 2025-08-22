<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Dom;

use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Dom\NodeCollection;
use Millancore\Pesto\Pesto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(Pesto::class)]
#[UsesClass(Document::class)]
#[UsesClass(Node::class)]
#[CoversClass(NodeCollection::class)]
final class NodeCollectionTest extends TestCase
{
    private NodeCollection $collection;
    private NodeCollection $emptyCollection;

    protected function setUp(): void
    {
        $html = '<div><p id="p1" class="test">First</p><span id="s1">Span</span><p id="p2" class="test">Second</p></div>';
        $pesto = new Pesto($html);

        $this->collection = $pesto->find('p');
        $this->emptyCollection = $pesto->find('.nonexistent');
    }

    public function test_count_returns_number_of_nodes(): void
    {
        $this->assertEquals(2, $this->collection->count());
        $this->assertEquals(0, $this->emptyCollection->count());
    }

    public function test_is_empty_returns_boolean(): void
    {
        $this->assertFalse($this->collection->isEmpty());
        $this->assertTrue($this->emptyCollection->isEmpty());
    }

    public function test_first_returns_first_node(): void
    {
        $firstNode = $this->collection->first();

        $this->assertInstanceOf(Node::class, $firstNode);
        $this->assertEquals('p1', $firstNode->getAttribute('id'));
    }

    public function test_each_iterates_over_nodes_in_reverse_order(): void
    {
        $nodes = [];
        $this->collection->each(function (Node $node) use (&$nodes) {
            $nodes[] = $node->getAttribute('id');
        });

        $this->assertEquals(['p2', 'p1'], $nodes);
    }

    public function test_each_does_not_execute_callback_on_empty_collection(): void
    {
        $callbackExecuted = false;
        $this->emptyCollection->each(function (Node $node) use (&$callbackExecuted) {
            $callbackExecuted = true;
        });

        $this->assertFalse($callbackExecuted);
    }

    public function test_iterator_yields_correct_node_instances(): void
    {
        $iterator = $this->collection->getIterator();
        $nodes = iterator_to_array($iterator);

        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(Node::class, $nodes[0]);
        $this->assertInstanceOf(Node::class, $nodes[1]);
        $this->assertEquals('p1', $nodes[0]->getAttribute('id'));
        $this->assertEquals('p2', $nodes[1]->getAttribute('id'));
    }
}
