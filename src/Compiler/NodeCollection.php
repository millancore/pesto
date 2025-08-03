<?php

namespace Millancore\Pesto\Compiler;

use IteratorAggregate;
use Dom\NodeList;
use Traversable;

class NodeCollection implements IteratorAggregate
{
    private NodeList $nodeList;

    public function __construct(NodeList $nodeList)
    {
        $this->nodeList = $nodeList;
    }

    public function isEmpty() : bool
    {
        return $this->nodeList->count() === 0;
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