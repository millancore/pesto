<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Filters;

use Millancore\Pesto\Filter\AsFilter;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AsFilter::class)]
class AsFilterTest extends TestCase
{
    public function testItConstructsWithAName(): void
    {
        $filter = new AsFilter('test_filter');
        $this->assertSame('test_filter', $filter->name);
    }

    public function testItIsAPhpAttribute(): void
    {
        $reflection = new \ReflectionClass(AsFilter::class);
        $attributes = $reflection->getattributes(\Attribute::class);

        $this->assertcount(1, $attributes);

        $attributesInstance = $attributes[0]->newinstance();
        $this->assertsame(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE, $attributesInstance->flags);
    }
}
