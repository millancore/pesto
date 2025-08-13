<?php

namespace Millancore\Pesto\Filter;

interface StackFilterInterface
{
    /**
     * @return array<string, callable>
     */
    public function getFilters() : array;
}