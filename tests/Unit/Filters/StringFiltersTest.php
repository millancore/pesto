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

    public function testIsInstanceOfFilterStack(): void
    {
        $this->assertInstanceOf(FilterStack::class, $this->filters);
        $this->assertIsArray($this->filters->getFilters());
    }

    #[DataProvider('provideUpper')]
    public function testUpper(string $expected, string $input): void
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
    public function testLower(string $expected, string $input): void
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
    public function testCapitalize(string $expected, string $input): void
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
    public function testTitle(string $expected, string $input): void
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
    public function testTrim(string $expected, string $input, string $characters = " \t\n\r\0\x0B"): void
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
    public function testNl2br(string $expected, string $input): void
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
    public function testStripTags(string $expected, string $input, string|array $allowed_tags = ''): void
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
    public function testSlug(string $expected, string $input, string $separator = '-'): void
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
    public function testJoin(string $expected, array $input, string $glue): void
    {
        $this->assertSame($expected, $this->filters->join($input, $glue));
    }

    public static function provideJoin(): array
    {
        return [
            ['a,b,c', ['a', 'b', 'c'], ','],
            ['a-b-c', ['a', 'b', 'c'], '-'],
        ];
    }
}
