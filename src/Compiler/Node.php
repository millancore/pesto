<?php

namespace Millancore\Pesto\Compiler;

use Dom\Node as DomNode;
use Dom\ProcessingInstruction;

class Node
{
    private DomNode $domNode;

    public function __construct(DomNode $domNode)
    {
        $this->domNode = $domNode;
    }

    public function getAttribute(string $name): ?string
    {
        if (!$this->hasAttribute($name)) {
            return null;
        }
        return $this->domNode->getAttribute($name);
    }

    public function hasAttribute(string $name): bool
    {
        return $this->domNode->hasAttribute($name);

    }

    public function getNextSibling(): ?Node
    {
        $sibling = $this->domNode->nextSibling;

        while ($sibling && $sibling->nodeType !== XML_ELEMENT_NODE) {
            $sibling = $sibling->nextSibling;
        }

        if ($sibling) {
            return new Node($sibling);
        }

        return null;

    }

    public function removeAttribute(string $name): void
    {
        $this->domNode->removeAttribute($name);
    }

    public function insertBefore(DomNode $newNode): void
    {
        $this->domNode->parentNode->insertBefore($newNode, $this->domNode);
    }

    public function insertAfter(DomNode $newNode): void
    {
        if ($this->domNode->nextSibling) {
            $this->domNode->parentNode->insertBefore($newNode, $this->domNode->nextSibling);
        } else {
            $this->domNode->parentNode->appendChild($newNode);
        }
    }

    public function createProcessingInstruction(string $target, string $data): ProcessingInstruction
    {
        return $this->domNode->ownerDocument->createProcessingInstruction($target, $data);
    }

    public function createPHPInstruction(string $data): ProcessingInstruction
    {
        return $this->domNode->ownerDocument->createProcessingInstruction('php', $data);
    }

    public function replaceWith(DomNode $node): void
    {
        if ($this->domNode->parentNode) {
            $this->domNode->parentNode->replaceChild($node, $this->domNode);
        }
    }

    public function getOuterXML(): string
    {
        return $this->domNode->ownerDocument->saveXml($this->domNode);
    }


    public function createDocumentFragment(): \Dom\DocumentFragment
    {
        return $this->domNode->ownerDocument->createDocumentFragment();
    }

    public function getFirstChild()
    {
        return $this->domNode->firstChild;
    }

    public function parentNode()
    {
        return $this->domNode->parentNode;
    }

    public function prepend(ProcessingInstruction $start)
    {
        $this->domNode->insertBefore($start, $this->domNode->firstChild);
    }

    public function append(ProcessingInstruction $end)
    {
        $this->domNode->appendChild($end);
    }


}