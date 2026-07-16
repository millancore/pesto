<?php

declare(strict_types=1);

namespace Millancore\Pesto\Console;

use Millancore\Pesto\Lint\TemplateLinter;

class CompileCommand extends Command
{
    /**
     * @param array<string> $args
     */
    public function run(array $args): int
    {
        $path = $args[0] ?? null;

        if ($path !== null && $path !== '-') {
            if (!is_file($path)) {
                $this->error(sprintf('Template "%s" not found.', $path));

                return 1;
            }

            $source = (string) file_get_contents($path);
        } else {
            $source = $path === null && $this->stdinIsInteractive() ? '' : $this->readStdin();

            if (trim($source) === '') {
                $this->error('Usage: pesto compile <template_path> (or pipe a template via stdin)');

                return 1;
            }

            $path = '<stdin>';
        }

        $result = (new TemplateLinter())->lint($source);

        if (!$result->isValid()) {
            $this->error(sprintf('Template %s failed validation:', $path));

            foreach ($result->errors as $error) {
                $this->error(' - '.$error);
            }

            return 1;
        }

        fwrite($this->stdout, $result->compiled.PHP_EOL);

        return 0;
    }
}
