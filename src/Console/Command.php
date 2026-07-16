<?php

declare(strict_types=1);

namespace Millancore\Pesto\Console;

abstract class Command
{
    protected const string COLOR_RED = '31';
    protected const string COLOR_GREEN = '32';

    /** @var resource */
    protected $stdout;

    /** @var resource */
    protected $stderr;

    /** @var resource */
    protected $stdin;

    /**
     * @param resource $stdout
     * @param resource $stderr
     * @param resource $stdin
     */
    public function __construct($stdout = STDOUT, $stderr = STDERR, $stdin = STDIN)
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->stdin = $stdin;
    }

    /**
     * @param array<string> $args
     */
    abstract public function run(array $args): int;

    protected function readStdin(): string
    {
        return (string) stream_get_contents($this->stdin);
    }

    protected function stdinIsInteractive(): bool
    {
        return @stream_isatty($this->stdin);
    }

    protected function line(string $message = '', ?string $color = null): void
    {
        fwrite($this->stdout, $this->colorize($message, $color, $this->stdout).PHP_EOL);
    }

    protected function error(string $message): void
    {
        fwrite($this->stderr, $this->colorize($message, self::COLOR_RED, $this->stderr).PHP_EOL);
    }

    /**
     * @param resource $stream
     */
    private function colorize(string $message, ?string $color, $stream): string
    {
        if ($color === null || !@stream_isatty($stream)) {
            return $message;
        }

        return "\033[".$color.'m'.$message."\033[0m";
    }
}
