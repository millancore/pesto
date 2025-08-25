<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\Htmlable;

readonly class Slot implements Htmlable
{
    public function __construct(
        public string $content,
    ) {
    }

    public function toHtml(): string
    {
        return $this->content;
    }
}
