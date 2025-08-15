<?php

declare(strict_types=1);

namespace Millancore\Pesto\Contract;

use Millancore\Pesto\Pesto;

interface CompilerPass
{
    public function compile(Pesto $pesto): void;
}
