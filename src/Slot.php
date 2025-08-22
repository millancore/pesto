<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\Htmlable;

readonly class Slot implements Htmlable, \Stringable
{
    public function __construct(
        private string $content,
    ) {
    }

    public function toHtml(): string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->content;
    }
}
