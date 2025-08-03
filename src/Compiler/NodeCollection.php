<?php

namespace Millancore\Pesto\Compiler;

use IteratorAggregate;
use Symfony\Component\DomCrawler\Crawler;
use Traversable;

class NodeCollection implements IteratorAggregate
{
    private Crawler $crawlerNodes;

    public function __construct(Crawler $crawlerNodes)
    {
        $this->crawlerNodes = $crawlerNodes;
    }

    public function each(callable $callback): void
    {
        for ($i = $this->crawlerNodes->count() - 1; $i >= 0; $i--) {
            $nodeWrapper = new Node($this->crawlerNodes->getNode($i));
            $callback($nodeWrapper);
        }
    }

    public function getIterator(): Traversable
    {
        foreach ($this->crawlerNodes as $domNode) {
            yield new Node($domNode);
        }
    }


}