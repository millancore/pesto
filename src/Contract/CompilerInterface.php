<?php

namespace Millancore\Pesto\Contract;

interface CompilerInterface
{
    public function compile(string $source): string;
}