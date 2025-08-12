<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class ContextPass implements CompilerPassInterface
{
    public function compile(Pesto $pesto): void
    {
        $this->walkNode($pesto->getDocument()->documentElement);
    }

    private function walkNode($node): void
    {
        if (!$node) return;

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                if (str_contains($attr->nodeValue, '{{')) {
                    $this->processAttribute($attr);
                }
            }
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE && str_contains($child->nodeValue, '{{')) {
                $this->processTextNode($child);
            }
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $this->walkNode($child);
            }
        }
    }

    private function processTextNode($textNode): void
    {
        $context = $this->getTextContext($textNode->parentNode);
        $textNode->nodeValue = $this->markContext($textNode->nodeValue, $context);
    }

    private function processAttribute($attr): void
    {
        $context = $this->getAttributeContext($attr->nodeName);
        $attr->nodeValue = $this->markContext($attr->nodeValue, $context);
    }

    private function getTextContext($parentElement): string
    {
        return match (strtolower($parentElement->nodeName ?? '')) {
            'script' => 'js',
            'style' => 'css',
            default => 'html'
        };
    }

    private function getAttributeContext(string $attrName): string
    {
        $attr = strtolower($attrName);

        return match (true) {
            in_array($attr, ['href', 'src', 'action']) => 'url',
            str_starts_with($attr, 'on') => 'js',
            $attr === 'style' => 'css',
            default => 'attr'
        };
    }

    private function markContext(string $content, string $context): string
    {
        if ($context === 'html') {
            return $content;
        }

        return preg_replace('/\{\{([^}|]+)(\|.*?)?\}\}/', '{{$1|' . $context . '$2}}', $content);
    }
}