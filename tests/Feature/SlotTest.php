<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\Exception\ViewException;
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

    public function test_render_slot_in_partial(): void
    {
        $slotTemplate = <<<HTML
    <main>
        <footer>{{\$footer| slot }}</footer>
        {{\$main | slot }}
    </main>
HTML;
        $layoutName = $this->createTemporaryTemplate('layout', $slotTemplate);

        $template = <<<HTML
<template php-partial="$layoutName">
    <h1>Title</h1>
    <p php-slot="footer">This is the footer</p>
</template>
HTML;

        $viewName = $this->createTemporaryTemplate('template', $template);

        $content = $this->env->make($viewName);

        $this->assertEquals(<<<HTML
<main>
        <footer><p>This is the footer</p></footer>
            <h1>Title</h1>
        </main>
HTML, (string) $content);
    }

    public function test_no_pass_slot_to_partial(): void
    {
        $slotTemplate = <<<HTML
<main>
<footer>{{\$footer }}</footer>
{{\$main | slot }}
</main>
HTML;
        $layoutName = $this->createTemporaryTemplate('layout', $slotTemplate);

        $template = <<<HTML
<template php-partial="$layoutName">
    <h1>Title</h1>
</template>
HTML;

        $viewName = $this->createTemporaryTemplate('incomplete', $template);

        $content = $this->env->make($viewName);

        $this->assertEquals(<<<HTML
<main>
<footer></footer>
    <h1>Title</h1>
</main>
HTML, (string) $content);
    }

    public function test_pass_slot_variable_from_data_to_partial(): void
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('The value of the slot is being passed from a different context.');

        $slotTemplate = <<<HTML
<main>
<footer>{{\$footer | slot }}</footer>
{{\$main | slot }}
</main>
HTML;

        $layoutName = $this->createTemporaryTemplate('layout', $slotTemplate);

        $template = <<<HTML
<template php-partial="$layoutName" php-with="['footer' => 'This is the footer']">
<h1>Title</h1>
</template>
HTML;
        $viewName = $this->createTemporaryTemplate('wrong_slot.php', $template);

        $this->env->make($viewName)->toHtml();
    }

    public function tearDown(): void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();
    }
}
