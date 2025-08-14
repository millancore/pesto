<?php

namespace Millancore\Pesto\Contract;

use Millancore\Pesto\Pesto;

interface CompilerPass
{
    public function compile(Pesto $pesto): void;

}