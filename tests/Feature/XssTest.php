<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class XssTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        $this->env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function tearDown(): void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();
    }

    private function assertTemplateOutput($template, $data, $expected): void
    {
        $templateID = $this->createTemporaryTemplate('xss_', $template);
        $content = $this->env->make($templateID, $data);
        $this->assertEquals($expected, (string) $content);
    }

    public function test_escape_html_context(): void
    {
        $xss = "<'\"&";
        $html = '<p>{{ $text }}</p>';
        $expected = '<p>&lt;&#039;&quot;&amp;</p>';

        $this->assertTemplateOutput($html, ['text' => $xss], $expected);
    }

    public function test_escape_attribute_context(): void
    {
        $xss = "<'\"&";
        $html = '<input value="{{ $text }}">';
        $expected = '<input value="&lt;&#039;&quot;&amp;"/>';

        $this->assertTemplateOutput($html, ['text' => $xss], $expected);
    }

    public function test_escape_js_context(): void
    {
        $xss = "'\"\n\u{2028}";
        $html = '<script>let foo = {{$text}};</script>';
        $expected = '<script>let foo = "\u0027\u0022\n\u2028";</script>';

        $this->assertTemplateOutput($html, ['text' => $xss], $expected);
    }

    public function test_escape_comment_context(): void
    {
        $xss = '-- -->';
        $html = '<p><!-- {{ $text }} --></p>';
        $expected = '<p><!-- - - - -&gt; --></p>';

        $this->assertTemplateOutput($html, ['text' => $xss], $expected);
    }

    public function test_escape_url_context(): void
    {
        $xss = "javascript:alert('xss')";
        $html = '<a href="{{$text}}">Link</a>';
        $expected = '<a href="">Link</a>';

        $this->assertTemplateOutput($html, ['text' => $xss], $expected);
    }

    public function test_if_user_force_raw_content(): void
    {
        $xss = "<'\"&";
        $html = '<p>{{ $text | raw }}</p>';
        $expected = '<p><\'"&</p>';

        $this->assertTemplateOutput($html, ['text' => $xss], $expected);
    }
}
