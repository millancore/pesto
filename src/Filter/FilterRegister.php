<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

use Millancore\Pesto\Contract\StackFilter;

class FilterRegister
{
    private array $filters = [];

    /**
     * @param StackFilter[] $stackFilters
     */
    public function __construct(array $stackFilters = [])
    {
        foreach ($stackFilters as $stack) {
            foreach ($stack->getFilters() as $name => $filter) {
                $this->add($name, $filter);
            }
        }
    }

    public function add(string $name, callable $callback): void
    {
        $this->filters[$name] = $callback;
    }

    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    public function get(string $name): callable
    {
        if ($this->has($name)) {
            return $this->filters[$name];
        }

        if (is_callable($name)) {
            return $name;
        }

        throw new \InvalidArgumentException(sprintf('Filter "%s" not found.', $name));
    }

    public function apply(mixed $expression, string|array $filter): mixed
    {
        if (is_string($filter)) {
            return $this->get($filter)($expression);
        }

        $filterName = array_shift($filter);

        return $this->get($filterName)($expression, ...$filter);
    }
}
