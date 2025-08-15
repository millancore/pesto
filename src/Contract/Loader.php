<?php

declare(strict_types=1);

namespace Millancore\Pesto\Contract;

interface Loader
{
    public function getSource(string $name): string;

    public function getPath(string $name): ?string;
}
