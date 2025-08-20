<?php

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\Htmlable;

readonly class Slot implements Htmlable
{
    public function __construct(
        private string $content
    ) {}

    public function toHtml(): string
    {
        return $this->content;
    }
}