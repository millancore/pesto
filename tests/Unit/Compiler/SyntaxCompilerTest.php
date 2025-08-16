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

    public function testCompileEscapedExpression(): void
    {
        $source = '<div>{{ $name }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, []) ?></div>', $result);
    }

    public function testCompileUnescapedExpression(): void
    {
        $source = '<div>{!! $html !!}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $html; ?></div>', $result);
    }

    public function testCompileEscapedExpressionWithSingleFilter(): void
    {
        $source = '<div>{{ $name | upper }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, [\'upper\']) ?></div>', $result);
    }

    public function testCompileEscapedExpressionWithMultipleFilters(): void
    {
        $source = '<div>{{ $name | upper | trim }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, [\'upper\', \'trim\']) ?></div>', $result);
    }

    public function testCompileEscapedExpressionWithFilterAndParameters(): void
    {
        $source = '<div>{{ $text | truncate:10 }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($text, [[\'truncate\', 10]]) ?></div>', $result);
    }

    public function testCompileEscapedExpressionWithFilterMultipleParameters(): void
    {
        $source = '<div>{{ $text | substr:0, 10 }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($text, [[\'substr\', 0, 10]]) ?></div>', $result);
    }

    public function testCompileMixedExpressions(): void
    {
        $source = '<div>{{ $name }} and {!! $html !!}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, []) ?> and <?= $html; ?></div>', $result);
    }

    public function testCompileWithWhitespaceInExpressions(): void
    {
        $source = '<div>{{  $name  |  upper  }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($name, [\'upper\']) ?></div>', $result);
    }

    public function testCompileWithNoExpressions(): void
    {
        $source = '<div>Just plain HTML</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div>Just plain HTML</div>', $result);
    }

    public function testCompileEmptyString(): void
    {
        $source = '';

        $result = $this->compiler->compile($source);

        $this->assertEquals('', $result);
    }

    public function testCompileMultipleEscapedExpressionsOnSameLine(): void
    {
        $source = '<div>{{ $first }} - {{ $second }}</div>';

        $result = $this->compiler->compile($source);

        $this->assertEquals('<div><?= $__pesto->output($first, []) ?> - <?= $__pesto->output($second, []) ?></div>', $result);
    }
}
