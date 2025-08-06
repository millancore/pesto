<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class YieldPass implements CompilerPassInterface
{
    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-yield]');

        $elements->each(function (Node $node) {
            $yieldName = $node->getAttribute('php-yield');
            $node->removeAttribute('php-yield');

            $defaultContent = $node->getOuterXML();

            $yieldNode = $node->createProcessingInstruction('php', 'echo $__pesto->yield("' . $yieldName . '");');

            $node->replaceWith($yieldNode);
        });
    }
}