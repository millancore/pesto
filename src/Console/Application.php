<?php

declare(strict_types=1);

namespace Millancore\Pesto\Console;

class Application
{
    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    /** @var resource */
    private $stdin;

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
     * @param array<string> $argv
     */
    public function run(array $argv): int
    {
        $command = $argv[1] ?? null;
        $args = array_slice($argv, 2);

        return match ($command) {
            'compile', '-c' => (new CompileCommand($this->stdout, $this->stderr, $this->stdin))->run($args),
            'lint' => (new LintCommand($this->stdout, $this->stderr, $this->stdin))->run($args),
            null, 'help', '-h', '--help' => $this->showHelp(),
            default => $this->showUnknownCommand($command),
        };
    }

    private function showHelp(): int
    {
        fwrite($this->stdout, <<<'HELP'
            Pesto - PHP View Engine

            Usage:
              pesto compile <template_path>   Validate and compile a template, print the result
              pesto -c <template_path>        Shorthand for compile
              pesto lint <path> [<path>...]   Validate template files or directories
              pesto help                      Show this help message

            Options:
              --views <dir>   Templates root; verifies php-partial references exist.
                              Without paths, lints the whole directory.

            Both commands read the template from stdin when no path is given (or with "-"):
              echo '<p p:if="$ok">Hi</p>' | pesto compile

            HELP);

        return 0;
    }

    private function showUnknownCommand(string $command): int
    {
        fwrite($this->stderr, sprintf('Unknown command "%s". Run "pesto help" for usage.', $command).PHP_EOL);

        return 1;
    }
}
