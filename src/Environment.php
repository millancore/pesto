<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Exception\ViewException;
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

    /**
     * @param list<mixed> $filters
     * @throws ViewException
     */
    public function output(mixed $expression, array $filters = []): string
    {
        // early return for slots skip extra filters
        if (in_array('slot', $filters)) {

            if (!$expression instanceof Slot) {
                throw new ViewException('The value of the slot is being passed from a different context.');
            }

            return $expression->content;
        }

        if (in_array('raw', $filters)) {
            array_pop($filters);
        }

        return (string) $this->filterRegistry->applyAll($expression, $filters);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $name, array $data): string
    {
        return $this->renderer->render($this, $name, $data);
    }

}
