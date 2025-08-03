<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class IfPass implements CompilerPassInterface
{
    public function compile(Pesto $pesto): void
    {
        $nodes = $pesto->find('[php-if]');

        $nodes->each(function (Node $node) {

            $anchorNode = $node;

            $condition = $node->getAttribute('php-if');
            $phpOpen = $node->createProcessingInstruction('php', 'if (' . $condition . '): ');

            $nextSibling = $node->getNextSibling();
            if ($nextSibling && $nextSibling->hasAttribute('php-else')) {
                $phpElse = $node->createProcessingInstruction('php', 'else: ');
                $nextSibling->insertBefore($phpElse);
                $nextSibling->removeAttribute('php-else');


                $anchorNode = $nextSibling;
            }

            $phpClose = $node->createProcessingInstruction('php', 'endif; ');

            $node->insertBefore($phpOpen);
            $anchorNode->insertAfter($phpClose);

            $node->removeAttribute('php-if');
        });
    }
    
    
    
    
}