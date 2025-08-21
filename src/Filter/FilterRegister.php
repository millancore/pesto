<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

use Millancore\Pesto\Contract\StackFilter;
use ReflectionClass;
use ReflectionMethod;

class FilterRegister
{
    /** @var array<string, callable> */
    private array $filters = [];

    /**
     * @param StackFilter[] $stackFilters
     */
    public function __construct(array $stackFilters = [])
    {
        foreach ($stackFilters as $stack) {
            $this->addStack($stack);
        }
    }

    public function addStack(StackFilter $stack): void
    {
        $reflection = new ReflectionClass($stack);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(AsFilter::class);

            foreach ($attributes as $attribute) {
                /** @var AsFilter $instance */
                $instance = $attribute->newInstance();
                $this->add($instance->name, [$stack, $method->getName()]);
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

    /**
     * @param string|array<string, array<mixed>> $filter
     */
    public function apply(mixed $expression, string|array $filter): mixed
    {
        if (is_string($filter)) {
            return $this->get($filter)($expression);
        }

        $filterName = array_shift($filter);

        return $this->get($filterName)($expression, ...$filter);
    }
}
