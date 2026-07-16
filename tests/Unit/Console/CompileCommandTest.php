<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Console;

use Millancore\Pesto\Console\CompileCommand;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CompileCommand::class)]
class CompileCommandTest extends TestCase
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

    public function test_compile_template_to_stdout(): void
    {
        $name = $this->createTemporaryTemplate('compile.php', '<li php-foreach="$items as $item">{{ $item }}</li>');

        $exitCode = (new CompileCommand($this->stdout, $this->stderr, $this->stdin))
            ->run([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(0, $exitCode);

        $output = $this->getStreamContents($this->stdout);
        $this->assertStringContainsString('<?php foreach($items as $item): ?>', $output);
        $this->assertStringContainsString('$__pesto->output($item', $output);
    }

    public function test_compile_template_from_stdin(): void
    {
        fwrite($this->stdin, '<p p:if="$ok">{{ $name }}</p>');
        rewind($this->stdin);

        $exitCode = (new CompileCommand($this->stdout, $this->stderr, $this->stdin))->run([]);

        $this->assertSame(0, $exitCode);

        $output = $this->getStreamContents($this->stdout);
        $this->assertStringContainsString('<?php if ($ok): ?>', $output);
        $this->assertStringContainsString('$__pesto->output($name', $output);
    }

    public function test_refuses_invalid_template(): void
    {
        $name = $this->createTemporaryTemplate('invalid.php', '<p php-if="$user->">{{ $name }</p>');

        $exitCode = (new CompileCommand($this->stdout, $this->stderr, $this->stdin))
            ->run([self::TEMPLATE_PATH.'/'.$name]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $this->getStreamContents($this->stdout));

        $errorOutput = $this->getStreamContents($this->stderr);
        $this->assertStringContainsString('failed validation', $errorOutput);
        $this->assertStringContainsString('Unclosed "{{" expression', $errorOutput);
        $this->assertStringContainsString('syntax error', $errorOutput);
    }

    public function test_fails_without_template_path_or_stdin(): void
    {
        $exitCode = (new CompileCommand($this->stdout, $this->stderr, $this->stdin))->run([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage: pesto compile', $this->getStreamContents($this->stderr));
    }

    public function test_fails_with_missing_template(): void
    {
        $exitCode = (new CompileCommand($this->stdout, $this->stderr, $this->stdin))
            ->run([self::TEMPLATE_PATH.'/does-not-exist.php']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('not found', $this->getStreamContents($this->stderr));
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
