<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;
use Millancore\Pesto\Exception\CompilerException;

class LayoutPass implements CompilerPassInterface
{
    /**
     * @throws CompilerException
     */
    public function compile(Pesto $pesto): void
    {
        $layoutNodes = $pesto->find('[php-layout]');

        if ($layoutNodes->isEmpty()) {
            return;
        }

        if ($layoutNodes->count() > 1) {
            throw new CompilerException('Only one [php-layout] tag is allowed per template.');
        }

        $container = $layoutNodes->first();
        $templateName = $container->getAttribute('php-layout');

        $openPI = $container->createPHPInstruction('$__pesto->startSlot(\'__default\'); ');
        $closePI = $container->createPHPInstruction('$__pesto->stopSlot(); echo $__pesto->render("' . $templateName . '", get_defined_vars()); ');

        $container->insertBefore($openPI);

        while ($child = $container->getFirstChild()) {
            $container->insertBefore($child);
        }


        $container->replaceWith($closePI);
    }
}