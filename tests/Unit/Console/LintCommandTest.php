<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Console;

use Millancore\Pesto\Console\LintCommand;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LintCommand::class)]
class LintCommandTest extends TestCase
{
    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    /** @var resource */
    private $stdin;

    protected function setUp(): void
    {
        $this->stdout = fopen('php://memory', 'w+');
        $this->stderr = fopen('php://memory', 'w+');
        $this->stdin = fopen('php://memory', 'w+');
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryTemplate();
    }

    public function test_lint_valid_template(): void
    {
        $name = $this->createTemporaryTemplate('valid.php', '<li php-foreach="$items as $item">{{ $item }}</li>');

        $exitCode = $this->runLint([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('no errors found', $this->getStreamContents($this->stdout));
    }

    public function test_lint_reports_php_syntax_error(): void
    {
        $name = $this->createTemporaryTemplate('syntax.php', '<p php-if="$user->">Broken</p>');

        $exitCode = $this->runLint([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('syntax error', $this->getStreamContents($this->stdout));
    }

    public function test_lint_reports_orphan_else_directive(): void
    {
        $name = $this->createTemporaryTemplate('orphan.html', <<<'HTML'
            <p php-if="$ok">Yes</p>
            <span>separator</span>
            <p php-else>No</p>
            HTML);

        $exitCode = $this->runLint([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Orphan "php-else" directive', $this->getStreamContents($this->stdout));
    }

    public function test_lint_reports_content_discarded_by_parser(): void
    {
        $name = $this->createTemporaryTemplate('swallowed.html', '<p p:if="true>{{$mama}</p>');

        $exitCode = $this->runLint([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('compiles to empty output', $this->getStreamContents($this->stdout));
    }

    public function test_lint_reports_unclosed_expression(): void
    {
        $name = $this->createTemporaryTemplate('unclosed.html', '<p>{{ $name }</p>');

        $exitCode = $this->runLint([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Unclosed "{{" expression', $this->getStreamContents($this->stdout));
    }

    public function test_lint_directory(): void
    {
        $this->createTemporaryTemplate('one.php', '<p p:if="$ok">Yes</p>');
        $this->createTemporaryTemplate('two.html', '<p>{{ $text | upper }}</p>');

        $exitCode = $this->runLint([self::TEMPLATE_PATH]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Linted 2 templates', $this->getStreamContents($this->stdout));
    }

    public function test_lint_template_from_stdin(): void
    {
        fwrite($this->stdin, '<p p:if="$ok">{{ $name }}</p>');
        rewind($this->stdin);

        $exitCode = $this->runLint([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('<stdin>', $this->getStreamContents($this->stdout));
    }

    public function test_lint_invalid_template_from_stdin(): void
    {
        fwrite($this->stdin, '<p php-if="$user->">Broken</p>');
        rewind($this->stdin);

        $exitCode = $this->runLint(['-']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('syntax error', $this->getStreamContents($this->stdout));
    }

    public function test_views_option_lints_whole_directory(): void
    {
        $exitCode = $this->runLint(['--views', self::VIEWS_PATH]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Linted 3 templates: no errors found', $this->getStreamContents($this->stdout));
    }

    public function test_views_option_reports_missing_partial(): void
    {
        $name = $this->createTemporaryTemplate('view.php', '<div php-partial="layouts/missing.php">x</div>');

        $exitCode = $this->runLint(['--views='.self::VIEWS_PATH, self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Partial "layouts/missing.php" not found', $this->getStreamContents($this->stdout));
    }

    public function test_views_option_requires_a_directory(): void
    {
        $exitCode = $this->runLint(['--views']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('"--views" requires a directory', $this->getStreamContents($this->stderr));
    }

    public function test_unknown_option_fails(): void
    {
        $exitCode = $this->runLint(['--nope', self::VIEWS_PATH]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Unknown option "--nope"', $this->getStreamContents($this->stderr));
    }

    public function test_fails_without_paths_or_stdin(): void
    {
        $exitCode = $this->runLint([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage: pesto lint', $this->getStreamContents($this->stderr));
    }

    public function test_fails_with_missing_path(): void
    {
        $exitCode = $this->runLint([self::TEMPLATE_PATH.'/missing-dir']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('not found', $this->getStreamContents($this->stderr));
    }

    /**
     * @param array<string> $args
     */
    private function runLint(array $args): int
    {
        return (new LintCommand($this->stdout, $this->stderr, $this->stdin))->run($args);
    }

    /**
     * @param resource $stream
     */
    private function getStreamContents($stream): string
    {
        rewind($stream);

        return (string) stream_get_contents($stream);
    }
}
