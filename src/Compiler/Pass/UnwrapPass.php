<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler\Pass;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Pesto;

class UnwrapPass implements CompilerPass
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
