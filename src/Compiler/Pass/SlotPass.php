<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class SlotPass implements CompilerPassInterface
{

    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-slot]');

        $elements->each(function (Node $element) {
            $sectionName = $element->getAttribute('php-slot');

            if (empty($sectionName)) {
                $sectionName = '__default';
            }

            $element->removeAttribute('php-slot');

            $start = $element->createPHPInstruction('$__pesto->startSlot("' . $sectionName . '") ;');
            $end = $element->createPHPInstruction( '$__pesto->stopSlot();');

            $element->prepend($start);
            $element->append($end);
        });
    }
}