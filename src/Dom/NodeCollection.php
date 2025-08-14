<?php

namespace Millancore\Pesto\Dom;

use Countable;
use Dom\NodeList;
use IteratorAggregate;
use Traversable;

class NodeCollection implements IteratorAggregate, Countable
{
    private NodeList $nodeList;

    public function __construct(NodeList $nodeList)
    {
        $this->nodeList = $nodeList;
    }

    public function isEmpty() : bool
    {
        return $this->count() === 0;
    }

    public function count() : int
    {
        return $this->nodeList->count();
    }

    public function first() : Node
    {
        return new Node($this->nodeList->item(0));
    }

    public function each(callable $callback): void
    {
        $count = $this->nodeList->count();
        for ($i = $count - 1; $i >= 0; $i--) {
            $callback(new Node($this->nodeList->item($i)));
        }
    }

    public function getIterator(): Traversable
    {
        for ($i = 0; $i < $this->nodeList->count(); $i++) {
            yield new Node($this->nodeList->item($i));
        }
    }

}