<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler;

use Millancore\Pesto\Compiler\SyntaxCompiler;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyntaxCompiler::class)]
class SyntaxCompilerTest extends TestCase
{
    private SyntaxCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new SyntaxCompiler();
    }

    public function test_compile_escaped_expression(): void
    {
        $source = '<div>{{ $name }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, []) ?></div>', $result);
    }

    public function test_compile_escaped_expression_with_single_filter(): void
    {
        $source = '<div>{{ $name | upper }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, [\'upper\']) ?></div>', $result);
    }

    public function test_compile_escaped_expression_with_multiple_filters(): void
    {
        $source = '<div>{{ $name | upper | trim }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, [\'upper\', \'trim\']) ?></div>', $result);
    }

    public function test_compile_escaped_expression_with_filter_and_parameters(): void
    {
        $source = '<div>{{ $text | truncate:10 }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($text, [[\'truncate\', 10]]) ?></div>', $result);
    }

    public function test_compile_escaped_expression_with_filter_multiple_parameters(): void
    {
        $source = '<div>{{ $text | substr:0, 10 }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($text, [[\'substr\', 0, 10]]) ?></div>', $result);
    }

    public function test_compile_with_whitespace_in_expressions(): void
    {
        $source = '<div>{{  $name  |  upper  }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, [\'upper\']) ?></div>', $result);
    }

    public function test_compile_with_no_expressions(): void
    {
        $source = '<div>Just plain HTML</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div>Just plain HTML</div>', $result);
    }

    public function test_compile_empty_string(): void
    {
        $source = '';

        $result = $this->compiler->compile($source);

        $this->assertEquals('', $result);
    }

    public function test_compile_multiple_escaped_expressions_on_same_line(): void
    {
        $source = '<div>{{ $first }} - {{ $second }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($first, []) ?> - <?= $__pesto->output($second, []) ?></div>', $result);
    }
}
