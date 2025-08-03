<?php

namespace Millancore\Pesto\Contract;

interface LoaderInterface
{
    public function getSource(string $name): string;
    public function getPath(string $name): ?string;
}