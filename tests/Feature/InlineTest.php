<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class InlineTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        $this->env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function test_render_foreach_and_if_inline(): void
    {
        $listTemplate = <<<PHP
<ul id="{{\$id}}">
    {{ \$main | slot }}
</ul>
PHP;
        $listTemplateName = $this->createTemporaryTemplate('list', $listTemplate);

        $compositionList = <<<PHP
<template php-partial="$listTemplateName" php-with="['id' => 'maria']">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>
        <template php-partial="$listTemplateName" php-with="['id' => 456]">
            <li php-foreach="range(1, 10) as \$number" php-if="\$number > 8">Item {{ \$number }}</li>
        </template>
    </li>
</template>
PHP;
        $viewName = $this->createTemporaryTemplate('composition-list', $compositionList);

        $content = $this->env->make($viewName)->toHtml();

        $this->assertStringContainsString('<ul id="maria">', $content);
        $this->assertStringNotContainsString('<li>Item 6</li>', $content);
        $this->assertStringContainsString('<li>Item 9</li>', $content);
        $this->assertStringContainsString('<li>Item 10</li>', $content);
    }

    public function test_render_foreach_and_if_inline_with_template(): void
    {
        $template = <<<PHP
<ul>
    <template php-foreach="range(1, 10) as \$number" php-if="\$number > 8">
        <li>Item {{ \$number }}</li>
    </template>
</ul>
PHP;
        $viewName = $this->createTemporaryTemplate('template', $template);

        $content = $this->env->make($viewName)->toHtml();

        $this->assertStringNotContainsString('<li>Item 6</li>', $content);
        $this->assertStringContainsString('<li>Item 9</li>', $content);
        $this->assertStringContainsString('<li>Item 10</li>', $content);
    }

    public function tearDown(): void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();
    }
}
