<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Dom\Node;

class Pass
{
    private const string TEMPLATE_TAG = 'template';
    private const string UNWRAP_ATTRIBUTE = 'php-inner';

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
