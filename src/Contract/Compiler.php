<?php

declare(strict_types=1);

namespace Millancore\Pesto\Contract;

interface Compiler
{
    public function compile(string $source): string;
}
