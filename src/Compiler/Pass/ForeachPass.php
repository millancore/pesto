<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class ForeachPass extends AbstractPass implements CompilerPassInterface
{

    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-foreach]');

        $elements->each(function (Node $element) {
            $foreachExpression = $element->getAttribute('php-foreach');
            $element->removeAttribute('php-foreach');


            $startInstruction = $element->createPHPInstruction("foreach($foreachExpression): ");
            $endInstruction = $element->createPHPInstruction('endforeach; ');

            $element->insertBefore($startInstruction);
            $element->insertAfter($endInstruction);

            $this->markTemplateForUnwrapping($element);
        });


    }
}