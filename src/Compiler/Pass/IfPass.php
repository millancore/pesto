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
        $nodes = $pesto->find($this->directiveSelector('if'));

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

        $this->removeDirective($node, 'if');
    }

    private function processIfCondition(Node $node): Node
    {
        $condition = $this->getDirective($node, 'if');
        $phpOpen = $node->createProcessingInstruction('php', 'if ('.$condition.'): ');
        $node->insertBefore($phpOpen);

        $this->markTemplateForUnwrapping($node);

        return $node;
    }

    private function processElseifConditions(Node $node, Node $anchorNode): Node
    {
        $nextSibling = $anchorNode->getNextSibling();

        while ($nextSibling && $this->hasDirective($nextSibling, 'elseif')) {
            $elseifCondition = $this->getDirective($nextSibling, 'elseif');
            $phpElseif = $node->createProcessingInstruction('php', 'elseif ('.$elseifCondition.'): ');
            $nextSibling->insertBefore($phpElseif);
            $this->removeDirective($nextSibling, 'elseif');

            $this->markTemplateForUnwrapping($nextSibling);

            $anchorNode = $nextSibling;
            $nextSibling = $nextSibling->getNextSibling();
        }

        return $anchorNode;
    }

    private function processElseCondition(Node $node, Node $anchorNode): Node
    {
        $nextSibling = $anchorNode->getNextSibling();

        if ($nextSibling && $this->hasDirective($nextSibling, 'else')) {
            $phpElse = $node->createProcessingInstruction('php', 'else: ');
            $nextSibling->insertBefore($phpElse);
            $this->removeDirective($nextSibling, 'else');

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
