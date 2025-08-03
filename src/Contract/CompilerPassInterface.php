<?php

namespace Millancore\Pesto\Contract;

use Millancore\Pesto\Compiler\Pesto;

interface CompilerPassInterface
{
    public function compile(Pesto $pesto): void;

}