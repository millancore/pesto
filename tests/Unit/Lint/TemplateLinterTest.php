<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Lint;

use Millancore\Pesto\Lint\TemplateLinter;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateLinter::class)]
class TemplateLinterTest extends TestCase
{
    public function test_valid_template_returns_compiled_output(): void
    {
        $result = (new TemplateLinter())->lint('<li p:foreach="$items as $item">{{ $item }}</li>');

        $this->assertTrue($result->isValid());
        $this->assertStringContainsString('<?php foreach($items as $item): ?>', (string) $result->compiled);
    }

    public function test_valid_layout_with_slots_and_filters(): void
    {
        $result = (new TemplateLinter())->lint(<<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>{{ $title | upper }}</title>
            </head>
            <body>
                <header>{{ $header | slot }}</header>
                <main>{{ $main | slot }}</main>
                <footer>{{ $year | date:'Y' }}</footer>
            </body>
            </html>
            HTML);

        $this->assertTrue($result->isValid());
    }

    public function test_valid_view_with_partial_slots_and_chained_filters(): void
    {
        $result = (new TemplateLinter())->lint(<<<'HTML'
            <template php-partial="layouts/app.php" php-with="['title' => 'Home']">
                <nav php-slot="header">
                    <a href="/about">{{ $about | title | trim }}</a>
                </nav>
                <section>
                    <li p:foreach="$items as $item" p:if="$item->visible">{{ $item->label | upper }}</li>
                </section>
            </template>
            HTML);

        $this->assertTrue($result->isValid());
    }

    public function test_existing_partial_reference_passes_with_views_root(): void
    {
        $result = (new TemplateLinter(self::VIEWS_PATH))
            ->lint((string) file_get_contents(self::VIEWS_PATH.'/home.php'));

        $this->assertTrue($result->isValid());
    }

    public function test_reports_missing_partial_reference_with_views_root(): void
    {
        $result = (new TemplateLinter(self::VIEWS_PATH))->lint(<<<'HTML'
            <div>
                <template php-partial="layouts/missing.php">x</template>
            </div>
            HTML);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString(
            'Partial "layouts/missing.php" not found in views directory',
            implode("\n", $result->errors),
        );
        $this->assertStringContainsString('on line 2', implode("\n", $result->errors));
    }

    public function test_partial_references_are_not_checked_without_views_root(): void
    {
        $result = (new TemplateLinter())->lint('<div p:partial="layouts/missing.php">x</div>');

        $this->assertTrue($result->isValid());
    }

    public function test_reports_broken_expression_in_php_with(): void
    {
        $result = (new TemplateLinter())->lint('<template php-partial="app.php" php-with="[\'title\' => ">x</template>');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('on line 1', implode("\n", $result->errors));
    }

    public function test_reports_php_syntax_error(): void
    {
        $result = (new TemplateLinter())->lint('<p php-if="$user->">Broken</p>');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('syntax error', implode("\n", $result->errors));
    }

    public function test_reports_unclosed_expression(): void
    {
        $result = (new TemplateLinter())->lint('<p>{{ $name }</p>');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Unclosed "{{" expression', implode("\n", $result->errors));
    }

    public function test_reports_content_discarded_by_parser(): void
    {
        $result = (new TemplateLinter())->lint('<p p:if="true>{{$mama}</p>');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('compiles to empty output', implode("\n", $result->errors));
    }

    public function test_reports_orphan_else_directive(): void
    {
        $result = (new TemplateLinter())->lint('<p php-if="$ok">Yes</p><span>x</span><p php-else>No</p>');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Orphan "php-else" directive on line 1', implode("\n", $result->errors));
    }

    public function test_directive_mentions_in_html_comments_are_not_located(): void
    {
        $result = (new TemplateLinter())->lint(<<<'HTML'
            <!-- this comment mentions php-else -->
            <p php-if="$ok">Yes</p>
            <hr>
            <p php-else>Orphan</p>
            HTML);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Orphan "php-else" directive on line 4', implode("\n", $result->errors));
    }

    public function test_syntax_error_reports_source_line_and_snippet(): void
    {
        $result = (new TemplateLinter())->lint(<<<'HTML'
            <ul>
                <li>first</li>
                <li php-if="$user->">broken</li>
                <li>last</li>
            </ul>
            HTML);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('on line 3: <li php-if="$user->">broken</li>', implode("\n", $result->errors));
    }

    public function test_unclosed_expression_reports_line(): void
    {
        $result = (new TemplateLinter())->lint(<<<'HTML'
            <div>
                <p>{{ $ok }}</p>
                <p>{{ $name }</p>
            </div>
            HTML);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Unclosed "{{" expression on line 3', implode("\n", $result->errors));
    }
}
