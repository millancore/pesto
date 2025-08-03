<?php

namespace Millancore\Pesto\Tests\Compiler;

use IteratorAggregate;
use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\NodeCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class NodeCollectionTest extends TestCase
{
    private NodeCollection $collection;

    public function setUp(): void
    {
        $html = '<div><p id="p1"></p><span id="s1"></span><p id="p2"></p></div>';
        $crawler = new Crawler($html);

        $this->collection = new NodeCollection($crawler->filter('p'));
    }

    public function test_get_iterator()
    {
        $this->assertInstanceOf(IteratorAggregate::class, $this->collection);
    }

    public function test_each_iterates_over_nodes_in_reverse_order(): void
    {
        $ids = [];

        $this->collection->each(function (Node $node) use (&$ids) {

            $this->assertInstanceOf(Node::class, $node);
            $ids[] = $node->getAttribute('id');
        });

        $this->assertEquals(['p2', 'p1'], $ids);
    }


}