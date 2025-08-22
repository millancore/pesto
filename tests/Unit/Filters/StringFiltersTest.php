<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Filters;

use Millancore\Pesto\Contract\FilterStack;
use Millancore\Pesto\Filter\StringFilters;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(StringFilters::class)]
class StringFiltersTest extends TestCase
{
    private StringFilters $filters;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filters = new StringFilters();
    }

    public function test_is_instance_of_filter_stack(): void
    {
        $this->assertInstanceOf(FilterStack::class, $this->filters);;
        $this->assertIsArray($this->filters->getFilters());
    }

    #[DataProvider('provideUpper')]
    public function test_upper(string $expected, string $input): void
    {
        $this->assertSame($expected, ($this->filters->getFilters()['upper'])($input));
    }

    public static function provideUpper(): array
    {
        return [
            ['HELLO', 'hello'],
            ['WORLD', 'World'],
        ];
    }

    #[DataProvider('provideLower')]
    public function test_lower(string $expected, string $input): void
    {
        $this->assertSame($expected, ($this->filters->getFilters()['lower'])($input));
    }

    public static function provideLower(): array
    {
        return [
            ['hello', 'HELLO'],
            ['world', 'World'],
        ];
    }

    #[DataProvider('provideCapitalize')]
    public function test_capitalize(string $expected, string $input): void
    {
        $this->assertSame($expected, ($this->filters->getFilters()['capitalize'])($input));
    }

    public static function provideCapitalize(): array
    {
        return [
            ['Hello world', 'hello world'],
            ['Hello world', 'HELLO WORLD'],
        ];
    }

    #[DataProvider('provideTitle')]
    public function test_title(string $expected, string $input): void
    {
        $this->assertSame($expected, ($this->filters->getFilters()['title'])($input));
    }

    public static function provideTitle(): array
    {
        return [
            ['Hello World', 'hello world'],
            ['Hello World', 'HELLO WORLD'],
        ];
    }

    #[DataProvider('provideTrim')]
    public function test_trim(string $expected, string $input, string $characters = " \t\n\r\0\x0B"): void
    {
        $this->assertSame($expected, $this->filters->trim($input, $characters));
    }

    public static function provideTrim(): array
    {
        return [
            ['hello', '  hello  '],
            ['hello', '..hello..', '.'],
        ];
    }

    #[DataProvider('provideNl2br')]
    public function test_nl2br(string $expected, string $input): void
    {
        $this->assertSame($expected, ($this->filters->getFilters()['nl2br'])($input));
    }

    public static function provideNl2br(): array
    {
        return [
            ["hello<br>\nworld", "hello\nworld"],
        ];
    }

    #[DataProvider('provideStripTags')]
    public function test_strip_tags(string $expected, string $input, string|array $allowed_tags = ''): void
    {
        $this->assertSame($expected, $this->filters->stripTags($input, $allowed_tags));
    }

    public static function provideStripTags(): array
    {
        return [
            ['hello', '<h1>hello</h1>'],
            ['<h1>hello</h1>', '<h1>hello</h1>', '<h1>'],
        ];
    }

    #[DataProvider('provideSlug')]
    public function test_slug(string $expected, string $input, string $separator = '-'): void
    {
        $this->assertSame($expected, $this->filters->slug($input, $separator));
    }

    public static function provideSlug(): array
    {
        return [
            ['hello-world', 'Hello World'],
            ['hello_world', 'Hello World', '_'],
            ['a-b-c', 'a b c'],
        ];
    }

    #[DataProvider('provideJoin')]
    public function test_join(string $expected, array $input, string $glue): void
    {
        $this->assertSame($expected, $this->filters->join($input, $glue));
    }

    public static function provideJoin(): array
    {
        return [
            ['a,b,c', ['a', 'b', 'c'], ','],
            ['a-b-c', ['a', 'b', 'c'], '-']
        ];
    }
}
