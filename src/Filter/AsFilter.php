<?php

namespace Millancore\Pesto\Filter;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class AsFilter
{
    public function __construct(
        public string $name,
    ) { }

}