<?php

declare(strict_types=1);

namespace Millancore\Pesto\Console;

use Millancore\Pesto\Lint\TemplateLinter;

class LintCommand extends Command
{
    private const array TEMPLATE_EXTENSIONS = ['html', 'php'];

    /**
     * @param array<string> $args
     */
    public function run(array $args): int
    {
        $viewsRoot = null;
        $paths = [];

        for ($i = 0; $i < count($args); ++$i) {
            $arg = $args[$i];

            if ($arg === '--views') {
                $viewsRoot = $args[++$i] ?? '';
            } elseif (str_starts_with($arg, '--views=')) {
                $viewsRoot = substr($arg, strlen('--views='));
            } elseif (str_starts_with($arg, '--')) {
                $this->error(sprintf('Unknown option "%s".', $arg));

                return 1;
            } else {
                $paths[] = $arg;

                continue;
            }

            if ($viewsRoot === '') {
                $this->error('Option "--views" requires a directory.');

                return 1;
            }
        }

        if ($viewsRoot !== null) {
            if (!is_dir($viewsRoot)) {
                $this->error(sprintf('Views directory "%s" not found.', $viewsRoot));

                return 1;
            }

            if ($paths === []) {
                $paths = [$viewsRoot];
            }
        }

        $linter = new TemplateLinter($viewsRoot);

        if ($paths === [] || $paths === ['-']) {
            return $this->lintStdin($paths === ['-'], $linter);
        }

        $files = [];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = $path;
            } elseif (is_dir($path)) {
                $files = array_merge($files, $this->findTemplates($path));
            } else {
                $this->error(sprintf('Path "%s" not found.', $path));

                return 1;
            }
        }

        if ($files === []) {
            $this->error('No templates found (.html, .php).');

            return 1;
        }

        $failedFiles = 0;

        foreach ($files as $file) {
            if (!$this->reportResult($file, $linter->lint((string) file_get_contents($file))->errors)) {
                ++$failedFiles;
            }
        }

        $this->line();
        $this->line(sprintf(
            'Linted %d template%s: %s.',
            count($files),
            count($files) === 1 ? '' : 's',
            $failedFiles === 0 ? 'no errors found' : sprintf('%d file%s with errors', $failedFiles, $failedFiles === 1 ? '' : 's'),
        ));

        return $failedFiles === 0 ? 0 : 1;
    }

    private function lintStdin(bool $explicit, TemplateLinter $linter): int
    {
        $source = !$explicit && $this->stdinIsInteractive() ? '' : $this->readStdin();

        if (trim($source) === '') {
            $this->error('Usage: pesto lint [--views <dir>] <path> [<path>...] (or pipe a template via stdin)');

            return 1;
        }

        return $this->reportResult('<stdin>', $linter->lint($source)->errors) ? 0 : 1;
    }

    /**
     * Prints the lint result for one template. Returns true when it passed.
     *
     * @param list<string> $errors
     */
    private function reportResult(string $label, array $errors): bool
    {
        if ($errors === []) {
            $this->line(' ✓ '.$label, self::COLOR_GREEN);

            return true;
        }

        $this->line(' ✗ '.$label, self::COLOR_RED);

        foreach ($errors as $error) {
            $this->line('   - '.$error);
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function findTemplates(string $directory): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        /** @var \SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (in_array(strtolower($fileInfo->getExtension()), self::TEMPLATE_EXTENSIONS, true)) {
                $files[] = $fileInfo->getPathname();
            }
        }

        sort($files);

        return $files;
    }
}
