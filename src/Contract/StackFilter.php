<?php

namespace Millancore\Pesto\Contract;

interface StackFilter
{
    /**
     * @return array<string, callable>
     */
    public function getFilters() : array;
}