<?php

namespace Millancore\Pesto\Contract;

interface Compiler
{
    public function compile(string $source): string;
}