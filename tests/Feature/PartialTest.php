<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class PartialTest extends TestCase
{
    private Environment $environment;

    public function setUp(): void
    {
        $this->environment = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function test_render_foreach_and_if_inline(): void
    {
        $this->refreshCache();

        $listTemplate = <<<PHP
<ul id="{{\$id}}">
    {!! \$slot !!}
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

        ob_start();
        $this->environment->render('composition-list.php');
        $content = ob_get_clean();

        $this->assertStringContainsString('<ul id="maria">', $content);
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
