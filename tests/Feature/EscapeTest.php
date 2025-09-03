<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class EscapeTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        $this->env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function test_it_can_escape_expressions(): void
    {
        $template = <<<'HTML'
<h1>{{ $title }}</h1>
<p>This should not be processed: @{{ name }}</p>
<p>This should be processed: {{ $name }}</p>
HTML;
        $viewName = $this->createTemporaryTemplate('escape-test', $template);

        $content = $this->env->make($viewName, ['title' => 'Hello World', 'name' => 'Pesto'])->toHtml();

        $this->assertStringContainsString('<h1>Hello World</h1>', $content);
        $this->assertStringContainsString('<p>This should not be processed: {{ name }}</p>', $content);
        $this->assertStringContainsString('<p>This should be processed: Pesto</p>', $content);
    }

    public function tearDown(): void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();
    }
}
