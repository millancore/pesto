<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\Htmlable;
use Stringable;

class View implements Htmlable, Stringable
{
    public function __construct(
        protected Environment $environment,
        protected string      $name,
        protected array       $data = [],
    ) { }

    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getData(): array
    {
        return $this->data;

    }

    public function __toString(): string
    {
        return $this->environment->render($this->name, $this->data);
    }

    public function toHtml(): string
    {
        return $this->__toString();
    }

}
