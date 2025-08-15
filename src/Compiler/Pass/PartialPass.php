<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Pesto;

class PartialPass extends Pass implements CompilerPass
{
    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-partial]');

        $elements->each(function (Node $element) {
            $partialView = $element->getAttribute('php-partial');
            $element->removeAttribute('php-partial');

            $data = $element->getAttribute('php-with') ?? '[]';
            $element->removeAttribute('php-with');

            $startInstruction = $element->createPHPInstruction('$__pesto->start("'.$partialView.'", '.$data.'); ');

            $endInstruction = $element->createPHPInstruction('$__pesto->end(); ');

            $element->insertBefore($startInstruction);
            $element->insertAfter($endInstruction);

            $this->markTemplateForUnwrapping($element);
        });
    }
}
