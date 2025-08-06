<?php

namespace Millancore\Pesto\Compiler\Pass;

use Dom\HTMLDocument;
use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;

class SectionPass implements CompilerPassInterface
{
    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-section]');

        $elements->each(function (Node $element) {
            $sectionName = $element->getAttribute('php-section');
            $element->removeAttribute('php-section');

            $start = $element->createProcessingInstruction('php', '$__pesto->startSection("' . $sectionName . '") ;');
            $end = $element->createProcessingInstruction('php', '$__pesto->stopSection();');

            $element->insertBefore($start);
            $element->insertAfter($end);
        });
    }
}