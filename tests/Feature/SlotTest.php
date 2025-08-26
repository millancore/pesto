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

        $template = <<<HTML
<template php-partial="slot2.php">
    <h1>Title</h1>
</template>
HTML;

        $this->createTemporaryTemplate('slot2.php', $slotTemplate);
        $this->createTemporaryTemplate('incomplete.php', $template);

        $content = $this->env->make('incomplete.php');

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

        $template = <<<HTML
<template php-partial="slot3.php" php-with="['footer' => 'This is the footer']">
<h1>Title</h1>
</template>
HTML;
        $this->createTemporaryTemplate('slot3.php', $slotTemplate);
        $this->createTemporaryTemplate('wrong_slot.php', $template);

        $this->env->make('wrong_slot.php')->toHtml();
    }

    public function tearDown(): void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();
    }
}
