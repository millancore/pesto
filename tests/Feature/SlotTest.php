<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class SlotTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        $this->env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function testRenderSlotInPartial(): void
    {
        $this->refreshCache();

        $slotTemplate = <<<HTML
    <main>
        <footer>{{\$footer}}</footer>
        {{\$slot}}
    </main>
HTML;

        $template = <<<HTML
<template php-partial="slot.php">
    <h1>Title</h1>
    <p php-slot="footer">This is the footer</p>
</template>
HTML;

        $this->createTemporaryTemplate('slot.php', $slotTemplate);
        $this->createTemporaryTemplate('template.php', $template);

        $content = $this->env->make('template.php');

        $this->assertEquals(<<<HTML
<main>
        <footer><p>This is the footer</p></footer>
            <h1>Title</h1>
        </main>
HTML, $content);
    }

    public function tearDown() : void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();

    }
}
