<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler;

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

    public function setUp(): void
    {
        $html = '<div><p id="p1"></p><span id="s1"></span><p id="p2"></p></div>';

        $this->collection = new Pesto($html)->find('p');
    }

    public function testCollectionIsIterable(): void
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->collection);
    }

    public function testCollectionIsCountable(): void
    {
        $this->assertInstanceOf(\Countable::class, $this->collection);
    }

    public function testCountReturnsNumbersOfNodes(): void
    {
        $this->assertEquals(2, $this->collection->count());
    }

    public function testCollectionIsNoEmpty(): void
    {
        $this->assertFalse($this->collection->isEmpty());
    }

    public function testEachIteratesOverNodesInReverseOrder(): void
    {
        $nodes = [];
        $this->collection->each(function (Node $node) use (&$nodes) {
            $nodes[] = $node->getAttribute('id');
        });

        $this->assertEquals(['p2', 'p1'], $nodes);
    }
}
