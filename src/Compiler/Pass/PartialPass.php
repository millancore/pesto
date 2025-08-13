<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class PartialPass extends AbstractPass implements CompilerPassInterface
{

    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-partial]');

        $elements->each(function (Node $element) {
            $partialView = $element->getAttribute('php-partial');
            $element->removeAttribute('php-partial');

            $data = $element->getAttribute('php-with') ?? '[]';
            $element->removeAttribute('php-with');


            $startInstruction = $element->createPHPInstruction('$__pesto->start("' . $partialView . '", ' . $data . '); ');

            $endInstruction = $element->createPHPInstruction('$__pesto->end(); ');

            $element->insertBefore($startInstruction);
            $element->insertAfter($endInstruction);

            $this->markTemplateForUnwrapping($element);
        });
    }
}