<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\ContextPass;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Pesto;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Document::class)]
#[UsesClass(Pesto::class)]
#[CoversClass(ContextPass::class)]
class ContextPassTest extends TestCase
{
    private ContextPass $pass;

    public function setUp(): void
    {
        $this->pass = new ContextPass();
    }

    public function test_compile_html_attribute_context(): void
    {
        $html = '<div id="{{$value}}">Hello</div>';
        $expected = '<div id="{{$value|attr}}">Hello</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_html_content_context(): void
    {
        $html = '<div>{{$value}}</div>';
        $expected = '<div>{{$value|escape}}</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_javascript_context(): void
    {
        $html = '<div onclick="alert(\'{{$message}}\')">Click</div>';
        $expected = '<div onclick="alert(\'{{$message|js}}\')">Click</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_url_context(): void
    {
        $html = '<a href="{{$url}}">Link</a>';
        $expected = '<a href="{{$url|url}}">Link</a>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_css_context(): void
    {
        $html = '<div style="color: {{$color}}">Text</div>';
        $expected = '<div style="color: {{$color|css}}">Text</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_with_extra_filters(): void
    {
        $html = '<div class="container {{$class|trim}}">Text</div>';
        $expected = '<div class="container {{$class|trim|attr}}">Text</div>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
