<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\ContextPass;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ContextPass::class)]
class ContextPassTest extends TestCase
{
    private ContextPass $pass;

    public function setUp(): void
    {
        $this->pass = new ContextPass();
    }

    public function testCompileHtmlAttributeContext(): void
    {
        $html = '<div id="{{$value}}">Hello</div>';
        $expected = '<div id="{{$value|attr}}">Hello</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileHtmlContentContext(): void
    {
        $html = '<div>{{$value}}</div>';
        $expected = '<div>{{$value|escape}}</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileJavascriptContext(): void
    {
        $html = '<div onclick="alert(\'{{$message}}\')">Click</div>';
        $expected = '<div onclick="alert(\'{{$message|js}}\')">Click</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileUrlContext(): void
    {
        $html = '<a href="{{$url}}">Link</a>';
        $expected = '<a href="{{$url|url}}">Link</a>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileCssContext(): void
    {
        $html = '<div style="color: {{$color}}">Text</div>';
        $expected = '<div style="color: {{$color|css}}">Text</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileWithExtraFilters(): void
    {
        $html = '<div class="container {{$class|trim}}">Text</div>';
        $expected = '<div class="container {{$class|trim|attr}}">Text</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
