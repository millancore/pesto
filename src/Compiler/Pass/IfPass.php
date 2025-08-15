<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Pesto;

class IfPass extends Pass implements CompilerPass
{
    public function compile(Pesto $pesto): void
    {
        $nodes = $pesto->find('[php-if]');

        $nodes->each(function (Node $node) {
            $this->compileIfStatement($node);
        });
    }

    private function compileIfStatement(Node $node): void
    {
        $anchorNode = $this->processIfCondition($node);
        $anchorNode = $this->processElseifConditions($node, $anchorNode);
        $anchorNode = $this->processElseCondition($node, $anchorNode);
        $this->closeIfStatement($node, $anchorNode);

        $node->removeAttribute('php-if');
    }

    private function processIfCondition(Node $node): Node
    {
        $condition = $node->getAttribute('php-if');
        $phpOpen = $node->createProcessingInstruction('php', 'if ('.$condition.'): ');
        $node->insertBefore($phpOpen);

        $this->markTemplateForUnwrapping($node);

        return $node;
    }

    private function processElseifConditions(Node $node, Node $anchorNode): Node
    {
        $nextSibling = $anchorNode->getNextSibling();

        while ($nextSibling && $nextSibling->hasAttribute('php-elseif')) {
            $elseifCondition = $nextSibling->getAttribute('php-elseif');
            $phpElseif = $node->createProcessingInstruction('php', 'elseif ('.$elseifCondition.'): ');
            $nextSibling->insertBefore($phpElseif);
            $nextSibling->removeAttribute('php-elseif');

            $this->markTemplateForUnwrapping($nextSibling);

            $anchorNode = $nextSibling;
            $nextSibling = $nextSibling->getNextSibling();
        }

        return $anchorNode;
    }

    private function processElseCondition(Node $node, Node $anchorNode): Node
    {
        $nextSibling = $anchorNode->getNextSibling();

        if ($nextSibling && $nextSibling->hasAttribute('php-else')) {
            $phpElse = $node->createProcessingInstruction('php', 'else: ');
            $nextSibling->insertBefore($phpElse);
            $nextSibling->removeAttribute('php-else');

            $this->markTemplateForUnwrapping($nextSibling);

            $anchorNode = $nextSibling;
        }

        return $anchorNode;
    }

    private function closeIfStatement(Node $node, Node $anchorNode): void
    {
        $phpClose = $node->createProcessingInstruction('php', 'endif; ');
        $anchorNode->insertAfter($phpClose);
    }
}
