<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsFilter
{
    public function __construct(
        public string $name,
    ) {
    }
}
