<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Filter\FilterRegister;

class Environment
{
    public PartialManager $partialManager;

    public function __construct(
        private readonly Renderer $renderer,
        private readonly FilterRegister $filterRegister,
    ) {
        $this->partialManager = new PartialManager();
    }

    public function make(string $name, array $data = []): View
    {
        return new View($this, $name, $data);
    }

    public function start(string $name, array $data = []): void
    {
        $this->partialManager->start($name, $data);
    }

    public function end(): void
    {
        $partial = $this->partialManager->end();

        echo $this->renderer->render($this, $partial['name'], $partial['data']);
    }

    public function slot(string $name): void
    {
        $this->partialManager->slot($name);
    }

    public function endSlot(): void
    {
        $this->partialManager->endSlot();
    }

    public function output(mixed $expression, array $filters = []): string
    {
        foreach ($filters as $filter) {
            $expression = $this->filterRegister->apply($expression, $filter);
        }

        return $expression;
    }

    public function render(string $name, array $data)
    {
        return $this->renderer->render($this, $name, $data);
    }
}
