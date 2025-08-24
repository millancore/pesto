<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Dom\Attr;
use Dom\Element;
use Dom\Node;
use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Pesto;

class ContextPass implements CompilerPass
{
    private const string ESCAPE_FILTER = 'escape';

    public function compile(Pesto $pesto): void
    {
        $this->walkNode($pesto->getDocument()->documentElement);
    }

    private function walkNode(?Element $node): void
    {
        if (!$node) {
            return;
        }

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                if (is_null($attr->nodeValue)) {
                    continue;
                }

                if (str_contains($attr->nodeValue, '{{')) {
                    $this->processAttribute($attr);
                }
            }
        }

        foreach ($node->childNodes as $child) {
            if (is_null($child->nodeValue)) {
                continue;
            }

            if ($child->nodeType === XML_TEXT_NODE && str_contains($child->nodeValue, '{{')) {
                $this->processTextNode($child);
            }

            if ($child->nodeType === XML_COMMENT_NODE && str_contains($child->nodeValue, '{{')) {
                $this->processCommentNode($child);
            }
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child instanceof Element) {
                $this->walkNode($child);
            }
        }
    }

    private function processCommentNode(Node $commentNode): void
    {
        if (is_null($commentNode->nodeValue)) {
            return;
        }

        $commentNode->nodeValue = $this->markContext(
            $commentNode->nodeValue,
            self::ESCAPE_FILTER.':\'comment\'',
        );
    }

    private function processTextNode(Node $textNode): void
    {
        if (is_null($textNode->nodeValue)) {
            return;
        }

        $context = $this->getTextContext($textNode->parentNode);
        $textNode->nodeValue = $this->markContext($textNode->nodeValue, $context);
    }

    private function processAttribute(Attr $attr): void
    {
        if (is_null($attr->nodeValue)) {
            return;
        }

        $context = $this->getAttributeContext($attr->nodeName);
        $attr->nodeValue = $this->markContext($attr->nodeValue, $context);
    }

    private function getTextContext(?Node $parentElement): string
    {
        return match (strtolower($parentElement->nodeName ?? '')) {
            'script' => self::ESCAPE_FILTER.':\'js\'',
            'style' => self::ESCAPE_FILTER.':\'css\'',
            default => self::ESCAPE_FILTER,
        };
    }

    private function getAttributeContext(string $attrName): string
    {
        $attr = strtolower($attrName);

        return match (true) {
            in_array($attr, ['href', 'src', 'action']) => self::ESCAPE_FILTER.':\'url\'',
            str_starts_with($attr, 'on') => self::ESCAPE_FILTER.':\'on_attribute\'',
            $attr === 'style' => self::ESCAPE_FILTER.':\'css\'',
            default => self::ESCAPE_FILTER,
        };
    }

    private function markContext(string $content, string $context): string
    {
        $inner = preg_replace('/\{\{([^}|]+)(\|.*?)?\}\}/', '{{$1$2|'.$context.'}}', $content);

        return $inner ?? $content;
    }
}
