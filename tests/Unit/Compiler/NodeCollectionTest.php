<?php

namespace Millancore\Pesto\Tests\Unit\Compiler;

use Countable;
use IteratorAggregate;
use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\NodeCollection;
use Millancore\Pesto\Compiler\Pesto;
use PHPUnit\Framework\TestCase;

final class NodeCollectionTest extends TestCase
{
    private NodeCollection $collection;

    public function setUp(): void
    {
        $html = '<div><p id="p1"></p><span id="s1"></span><p id="p2"></p></div>';

        $this->collection = new Pesto($html)->find('p');
    }

    public function test_collection_is_iterable() : void
    {
        $this->assertInstanceOf(IteratorAggregate::class, $this->collection);
    }

    public function test_collection_is_countable() : void
    {
        $this->assertInstanceOf(Countable::class, $this->collection);;
    }

    public function test_count_returns_numbers_of_nodes() : void
    {
        $this->assertEquals(2, $this->collection->count());
    }

    public function test_collection_is_no_empty() : void
    {
        $this->assertFalse($this->collection->isEmpty());
    }

    public function test_each_iterates_over_nodes_in_reverse_order() : void
    {
        $nodes = [];
        $this->collection->each(function (Node $node) use (&$nodes) {
            $nodes[] = $node->getAttribute('id');
        });

        $this->assertEquals(['p2', 'p1'], $nodes);
    }
}
