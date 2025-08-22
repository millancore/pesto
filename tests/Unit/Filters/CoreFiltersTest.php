<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Filters;

use Millancore\Pesto\Contract\FilterStack;
use Millancore\Pesto\Contract\Htmlable;
use Millancore\Pesto\Filter\CoreFilters;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CoreFilters::class)]
class CoreFiltersTest extends TestCase
{
    private CoreFilters $filters;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filters = new CoreFilters();
    }

    public function testIsInstanceOfFilterStack(): void
    {
        $this->assertInstanceOf(FilterStack::class, $this->filters);
        $this->assertIsArray($this->filters->getFilters());
    }

    #[DataProvider('provideEscape')]
    public function testEscapeFilter(string $expected, mixed $input): void
    {
        $this->assertSame($expected, $this->filters->escape($input));
    }

    public static function provideEscape(): array
    {
        return [
            ['&lt;script&gt;', '<script>'],
            ['test', 'test'],
            ['foo bar', new class implements Htmlable {
                public function toHtml(): string
                {
                    return 'foo bar';
                }
            }],
            ['to string', new class {
                public function __toString(): string
                {
                    return 'to string';
                }
            }],
        ];
    }

    public function testTryToEscapeArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot escape array in HTML context. Use {!! !!} for raw output or explicitly convert to string');

        $this->filters->escape([]);
    }

    public function testTryToEscapeObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('To print an object, implement __toString() method in it, or implement Htmlable');

        $this->filters->escape(new \stdClass());
    }

    #[DataProvider('provideEscapeUrl')]
    public function testEscapeUrl(string $expected, string $input): void
    {
        $this->assertSame($expected, $this->filters->escapeUrl($input));
    }

    public static function provideEscapeUrl(): array
    {
        return [
            ['https%3A%2F%2Fexample.com%2F%3Fa%3Db%26c%3Dd', 'https://example.com/?a=b&c=d'],
        ];
    }

    #[DataProvider('provideEscapeJs')]
    public function testEscapeJs(string $expected, mixed $input): void
    {
        $this->assertSame($expected, $this->filters->escapeJs($input));
    }

    public static function provideEscapeJs(): array
    {
        return [
            ['"hello"', 'hello'],
            ['123', 123],
            ['{"a":"b"}', ['a' => 'b']],
            ['null', NAN],
        ];
    }

    #[DataProvider('provideEscapeCss')]
    public function testEscapeCss(string $expected, string $input): void
    {
        $this->assertSame($expected, $this->filters->escapeCss($input));
    }

    public static function provideEscapeCss(): array
    {
        return [
            ['abcdefg123-_#%.', 'abcdefg123-_#%.!@$^*()'],
        ];
    }
}
