<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Filters;

use Millancore\Pesto\Contract\FilterStack;
use Millancore\Pesto\Exception\FilterException;
use Millancore\Pesto\Filter\AsFilter;
use Millancore\Pesto\Filter\CoreFilters;
use Millancore\Pesto\Filter\FilterRegistry;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FilterRegistry::class)]
#[UsesClass(AsFilter::class)]
#[UsesClass(CoreFilters::class)]
class FilterRegistryTest extends TestCase
{
    public function test_add_and_get_filter(): void
    {
        $registry = new FilterRegistry();
        $registry->add('uppercase', 'strtoupper');

        $this->assertSame('strtoupper', $registry->get('uppercase'));
    }

    public function test_get_non_existent_filter_throws_exception(): void
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Filter "non_existent" not found.');

        $registry = new FilterRegistry();
        $registry->get('non_existent');
    }

    public function test_has_filter(): void
    {
        $registry = new FilterRegistry();
        $registry->add('lowercase', 'strtolower');

        $this->assertTrue($registry->has('lowercase'));
        $this->assertFalse($registry->has('uppercase'));
    }

    public function test_apply_simple_filter(): void
    {
        $registry = new FilterRegistry();
        $registry->add('uppercase', 'strtoupper');

        $this->assertSame('HELLO', $registry->apply('Hello', 'uppercase'));
    }

    public function test_apply_filter_with_arguments(): void
    {
        $registry = new FilterRegistry();
        $registry->add('add', fn ($initial, $value) => $initial + $value);

        $this->assertSame(15, $registry->apply(10, ['add', 5]));
    }

    public function test_it_registers_filters_from_provider_with_attribute(): void
    {
        $provider = new class {
            #[AsFilter(name: 'my_filter')]
            public function myFilterMethod(string $value): string
            {
                return 'filtered_'.$value;
            }
        };

        $registry = new FilterRegistry([$provider]);

        $this->assertTrue($registry->has('my_filter'));
        $this->assertSame('filtered_test', $registry->apply('test', 'my_filter'));
    }

    public function test_it_registers_filters_from_provider_with_contract(): void
    {
        $provider = new class implements FilterStack {
            public function getFilters(): array
            {
                return [
                    'contract_filter' => fn ($value) => 'contract_'.$value,
                ];
            }
        };

        $registry = new FilterRegistry([$provider]);

        $this->assertTrue($registry->has('contract_filter'));
        $this->assertSame('contract_test', $registry->apply('test', 'contract_filter'));
    }

    public function test_try_overriding_filter_with_same_name_throws_exception(): void
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Filter "escape" already exists.');

        $customProvider = new class {
            #[AsFilter(name: 'escape')]
            public function malisiusEscape($value): string
            {
                return $value;
            }
        };

        $registry = new FilterRegistry([new CoreFilters(), $customProvider]);

        $registry->apply("<\"'&", 'escape');
    }
}
