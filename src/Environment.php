<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Filter\FilterRegistry;

class Environment
{
    use PartialHandler;

    public function __construct(
        private readonly Renderer $renderer,
        private readonly FilterRegistry $filterRegistry,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function make(string $name, array $data = []): View
    {
        return new View($this, $name, $data);
    }

    public function end(): void
    {
        $partial = $this->endPartial();
        echo $this->renderer->render($this, $partial['name'], $partial['data']);
    }

    /**
     * @param list<mixed> $filters
     */
    public function output(mixed $expression, array $filters = []): string
    {
        foreach ($filters as $filter) {
            $expression = $this->filterRegistry->apply($expression, $filter);
        }

        return (string) $expression;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $name, array $data): string
    {
        return $this->renderer->render($this, $name, $data);
    }
}
