<?php

declare(strict_types=1);

namespace Millancore\Pesto\Contract;

interface Htmlable
{
    public function toHtml(): string;
}
