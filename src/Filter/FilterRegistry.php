<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

use Millancore\Pesto\Contract\FilterStack;
use Millancore\Pesto\Exception\FilterException;
use ReflectionMethod;

class FilterRegistry
{
    /** @var array<string, callable> */
    private array $filters = [];

    /**
     * @param iterable<object> $filterProviders los objetos que contienen los métodos de filtro
     */
    public function __construct(iterable $filterProviders = [])
    {
        foreach ($filterProviders as $provider) {
            if ($provider instanceof FilterStack) {
                $this->registerProviderFromContract($provider);
                continue;
            }

            $this->registerProviderFromAttribute($provider);
        }
    }

    /**
     * Use Contract to prevent Reflection overhead.
     */
    private function registerProviderFromContract(FilterStack $filterStack): void
    {
        foreach ($filterStack->getFilters() as $name => $callback) {
            $this->add($name, $callback);
        }
    }

    private function registerProviderFromAttribute(object $provider): void
    {
        $reflection = new \ReflectionClass($provider);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(AsFilter::class);
            foreach ($attributes as $attribute) {
                /** @var AsFilter $instance */
                $instance = $attribute->newInstance();
                $this->add($instance->name, [$provider, $method->getName()]);
            }
        }
    }

    /**
     * @param callable(mixed...):mixed $callback
     * @throws FilterException
     */
    public function add(string $name, callable $callback): void
    {
        if (isset($this->filters[$name])) {
            throw new FilterException(sprintf('Filter "%s" already exists.', $name));
        }

        $this->filters[$name] = $callback;
    }

    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    /**
     * @throws FilterException
     */
    public function get(string $name): callable
    {
        if (!$this->has($name)) {
            throw new FilterException(sprintf('Filter "%s" not found.', $name));
        }

        return $this->filters[$name];
    }

    /**
     * @param string|array<mixed> $filter
     */
    public function apply(mixed $expression, string|array $filter): mixed
    {
        if (is_string($filter)) {
            return $this->get($filter)($expression);
        }

        $filterName = array_shift($filter);

        return $this->get($filterName)($expression, ...$filter);
    }

    public function applyAll(mixed $expression, array $filters): mixed
    {
        foreach ($filters as $filter) {
            $expression = $this->apply($expression, $filter);
        }

        return $expression;
    }
}
