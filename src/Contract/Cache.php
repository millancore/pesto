<?php

namespace Millancore\Pesto\Contract;

interface Cache
{
    public function getCompiledPath(string $name): string;
    public function write(string $path, string $content): void;
    public function isFresh(string $name): bool;
}