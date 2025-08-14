<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Pesto;

class ForeachPass extends Pass implements CompilerPass
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