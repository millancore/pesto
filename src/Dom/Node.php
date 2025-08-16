<?php

declare(strict_types=1);

namespace Millancore\Pesto\Dom;

use Dom\DocumentFragment;
use Dom\Element;
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
        if ($this->domNode instanceof Element) {
            return $this->domNode->getAttribute($name);
        }

        return null;
    }

    public function setAttribute(string $name, string $value): void
    {
        if ($this->domNode instanceof Element) {
            $this->domNode->setAttribute($name, $value);
        }
    }

    public function getDomNode(): DomNode
    {
        return $this->domNode;
    }

    public function hasAttribute(string $name): bool
    {
        if ($this->domNode instanceof Element) {
            return $this->domNode->hasAttribute($name);
        }

        return false;
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
        if ($this->domNode instanceof Element) {
            $this->domNode->removeAttribute($name);
        }
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

    public function createDocumentFragment(): DocumentFragment
    {
        return $this->domNode->ownerDocument->createDocumentFragment();
    }

    public function unwrap(): void
    {
        // Move all children to be siblings before the current node
        while ($this->domNode->hasChildNodes()) {
            $child = $this->domNode->firstChild;
            // Detach the child from the current node and get it back
            $this->domNode->removeChild($child);
            // Insert the child before the current node
            $this->domNode->parentNode->insertBefore($child, $this->domNode);
        }

        // Remove the now-empty node
        $this->domNode->parentNode->removeChild($this->domNode);
    }

    public function after(DomNode $newNode): void
    {
        if ($this->domNode->nextSibling) {
            $this->domNode->parentNode->insertBefore($newNode, $this->domNode->nextSibling);
        } else {
            $this->domNode->parentNode->appendChild($newNode);
        }
    }
}
