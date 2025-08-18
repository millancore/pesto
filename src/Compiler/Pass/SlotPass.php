<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Pesto;

class SlotPass extends Pass implements CompilerPass
{
    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-slot]');

        $elements->each(function (Node $element) {
            $slot = $element->getAttribute('php-slot');
            $element->removeAttribute('php-slot');

            $startInstruction = $element->createPHPInstruction('$__pesto->slot("'.$slot.'"); ');

            $endInstruction = $element->createPHPInstruction('$__pesto->endSlot(); ');

            $element->insertBefore($startInstruction);
            $element->insertAfter($endInstruction);

            $this->markTemplateForUnwrapping($element);
        });
    }
}
