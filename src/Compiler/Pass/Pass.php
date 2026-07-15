<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Dom\Node;

class Pass
{
    private const string TEMPLATE_TAG = 'template';
    private const string UNWRAP_ATTRIBUTE = 'php-inner';

    /** Long form first: `php-*` wins when both forms are present. */
    private const array PREFIXES = ['php-', 'p:'];

    protected function directiveSelector(string $directive): string
    {
        $selectors = array_map(
            fn (string $prefix) => '['.str_replace(':', '\\:', $prefix.$directive).']',
            self::PREFIXES,
        );

        return implode(', ', $selectors);
    }

    protected function hasDirective(Node $node, string $directive): bool
    {
        foreach (self::PREFIXES as $prefix) {
            if ($node->hasAttribute($prefix.$directive)) {
                return true;
            }
        }

        return false;
    }

    protected function getDirective(Node $node, string $directive): ?string
    {
        foreach (self::PREFIXES as $prefix) {
            if ($node->hasAttribute($prefix.$directive)) {
                return $node->getAttribute($prefix.$directive);
            }
        }

        return null;
    }

    protected function removeDirective(Node $node, string $directive): void
    {
        foreach (self::PREFIXES as $prefix) {
            $node->removeAttribute($prefix.$directive);
        }
    }

    protected function markTemplateForUnwrapping(Node $node): void
    {
        if ($this->isTemplateNode($node)) {
            $node->setAttribute(self::UNWRAP_ATTRIBUTE, '');
        }
    }

    private function isTemplateNode(Node $node): bool
    {
        return strtolower($node->getDomNode()->nodeName) === self::TEMPLATE_TAG;
    }
}
