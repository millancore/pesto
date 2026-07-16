<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Console;

use Millancore\Pesto\Console\Application;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
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

    public function test_shows_help_without_arguments(): void
    {
        $exitCode = (new Application($this->stdout, $this->stderr, $this->stdin))->run(['pesto']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Usage:', $this->getStreamContents($this->stdout));
    }

    public function test_short_compile_alias_dispatches_to_compile(): void
    {
        $exitCode = (new Application($this->stdout, $this->stderr, $this->stdin))->run(['pesto', '-c']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage: pesto compile', $this->getStreamContents($this->stderr));
    }

    public function test_unknown_command_fails(): void
    {
        $exitCode = (new Application($this->stdout, $this->stderr, $this->stdin))->run(['pesto', 'unknown']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Unknown command "unknown"', $this->getStreamContents($this->stderr));
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
