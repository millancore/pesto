<?php

declare(strict_types=1);

namespace Millancore\Pesto\Contract;

interface StackFilter
{
    /**
     * @return array<string, callable>
     */
    public function getFilters(): array;
}
