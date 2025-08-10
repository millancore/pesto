<?php

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Compiler\Node;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerPassInterface;


class UnwrapPass implements CompilerPassInterface
{
    public function compile(Pesto $pesto): void
    {
        $elements = $pesto->find('[php-inner]');

        $elements->each(function (Node $element) {

            $element->removeAttribute('php-inner');

            $element->unwrap();
        });
    }
}