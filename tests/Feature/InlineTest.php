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

    public function testRenderForeachAndIfInline(): void
    {
        $this->refreshCache();

        $listTemplate = <<<PHP
<ul id="{{\$id}}">
    {{ \$slot }}
</ul>
PHP;

        $compositionList = <<<PHP
<template php-partial="list.php" php-with="['id' => 'maria']">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>
        <template php-partial="list.php" php-with="['id' => 456]">
            <li php-foreach="range(1, 10) as \$number" php-if="\$number > 8">Item {{ \$number }}</li>
        </template>
    </li>
</template>
PHP;

        $this->createTemporaryTemplate('list.php', $listTemplate);
        $this->createTemporaryTemplate('composition-list.php', $compositionList);

        $content = $this->env->make('composition-list.php')->toHtml();

        $this->assertStringContainsString('<ul id="maria">', $content);
        $this->assertStringNotContainsString('<li>Item 6</li>', $content);
        $this->assertStringContainsString('<li>Item 9</li>', $content);
        $this->assertStringContainsString('<li>Item 10</li>', $content);
    }

    public function testRenderForeachAndIfInlineWithTemplate(): void
    {
        $this->refreshCache();

        $template = <<<PHP
<ul>
    <template php-foreach="range(1, 10) as \$number" php-if="\$number > 8">
        <li>Item {{ \$number }}</li>
    </template>
</ul>
PHP;
        $this->createTemporaryTemplate('template.php', $template);

        $content = $this->env->make('template.php')->toHtml();

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
