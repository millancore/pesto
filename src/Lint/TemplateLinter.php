<?php

declare(strict_types=1);

namespace Millancore\Pesto\Lint;

use Millancore\Pesto\Compiler\PestoCompiler;

class TemplateLinter
{
    /** Directive attributes left in the compiled output were never processed. */
    private const string UNPROCESSED_DIRECTIVE_PATTERN = '/(?:php-|p:)(?:elseif|else|if|foreach|partial|with|slot)(?==")/';

    private const string PARTIAL_REFERENCE_PATTERN = '/(?:php-|p:)partial\s*=\s*(["\'])(.*?)\1/';

    /**
     * @param string|null $viewsRoot templates directory used to verify that
     *                               php-partial references exist
     */
    public function __construct(
        private ?string $viewsRoot = null,
    ) {
    }

    public function lint(string $source): LintResult
    {
        try {
            // Engine validation is off: the linter runs its own richer
            // versions of those checks and reports all errors at once.
            $compiled = (new PestoCompiler(validate: false))->compile($source);
        } catch (\Throwable $e) {
            return new LintResult(null, ['Compilation failed: '.$e->getMessage()]);
        }

        $errors = [];

        $unclosedLine = $this->findUnclosedExpressionLine($source);

        if ($unclosedLine !== null) {
            $errors[] = sprintf('Unclosed "{{" expression on line %d: missing matching "}}".', $unclosedLine);
        }

        if (trim($source) !== '' && trim($compiled) === '') {
            $errors[] = 'Template compiles to empty output: the HTML parser discarded the content (usually an unterminated attribute quote or tag).';

            return new LintResult($compiled, $errors);
        }

        foreach ($this->findUnprocessedDirectives($compiled) as $directive) {
            $location = $this->formatLines($this->directiveLines($source, $directive));

            $errors[] = str_ends_with($directive, 'else') || str_ends_with($directive, 'elseif')
                ? sprintf('Orphan "%s" directive%s: it must be an immediate sibling of a "php-if" element.', $directive, $location)
                : sprintf('Unprocessed "%s" directive%s.', $directive, $location);
        }

        $syntaxError = $this->checkPhpSyntax($compiled);

        if ($syntaxError !== null) {
            $errors[] = $this->mapSyntaxErrorToSource($syntaxError, $source);
        }

        return new LintResult($compiled, [...$errors, ...$this->checkPartialReferences($source)]);
    }

    /**
     * Verifies php-partial references against the views root, mirroring
     * FileSystemLoader::getPath(). Skipped when no views root is set.
     *
     * @return list<string>
     */
    private function checkPartialReferences(string $source): array
    {
        if ($this->viewsRoot === null) {
            return [];
        }

        $source = $this->stripComments($source);
        $errors = [];

        preg_match_all(self::PARTIAL_REFERENCE_PATTERN, $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match[2][0];

            if (!is_file($this->viewsRoot.'/'.$name)) {
                $errors[] = sprintf(
                    'Partial "%s" not found in views directory "%s" on line %d.',
                    $name,
                    $this->viewsRoot,
                    $this->lineAt($source, $match[0][1]),
                );
            }
        }

        return $errors;
    }

    /**
     * A "{{" without a following "}}" never gets closed. Pairs are consumed
     * left to right, mirroring the compiler's non-greedy matching.
     */
    private function findUnclosedExpressionLine(string $source): ?int
    {
        $pos = 0;

        while (($start = strpos($source, '{{', $pos)) !== false) {
            $end = strpos($source, '}}', $start + 2);

            if ($end === false) {
                return $this->lineAt($source, $start);
            }

            $pos = $end + 2;
        }

        return null;
    }

    /**
     * Blanks out HTML comments, keeping newlines so offsets stay on the
     * same lines.
     */
    private function stripComments(string $source): string
    {
        return (string) preg_replace_callback(
            '/<!--.*?-->/s',
            fn (array $match) => str_repeat("\n", substr_count($match[0], "\n")),
            $source,
        );
    }

    private function lineAt(string $source, int $offset): int
    {
        return $offset === 0 ? 1 : substr_count($source, "\n", 0, $offset) + 1;
    }

    /**
     * Source lines where the directive attribute appears. HTML comments are
     * blanked out (newlines kept) so mentions inside them don't count.
     *
     * @return list<int>
     */
    private function directiveLines(string $source, string $directive): array
    {
        $source = $this->stripComments($source);

        preg_match_all('/'.preg_quote($directive, '/').'(?![a-z])/', $source, $matches, PREG_OFFSET_CAPTURE);

        $lines = array_map(
            fn (array $match) => $this->lineAt($source, $match[1]),
            $matches[0],
        );

        return array_values(array_unique($lines));
    }

    /**
     * @param list<int> $lines
     */
    private function formatLines(array $lines): string
    {
        return match (count($lines)) {
            0 => '',
            1 => ' on line '.$lines[0],
            default => ' on lines '.implode(', ', $lines),
        };
    }

    /**
     * The serializer preserves newlines, so a line in the compiled output
     * corresponds to the same line in the source.
     */
    private function mapSyntaxErrorToSource(string $message, string $source): string
    {
        if (!preg_match('/ in compiled template on line (\d+)$/', $message, $matches)) {
            return $message;
        }

        $line = (int) $matches[1];
        $sourceLines = explode("\n", $source);

        if (!isset($sourceLines[$line - 1])) {
            return $message;
        }

        $snippet = trim($sourceLines[$line - 1]);

        if (strlen($snippet) > 80) {
            $snippet = substr($snippet, 0, 80).'…';
        }

        return (string) preg_replace(
            '/ in compiled template on line \d+$/',
            sprintf(' on line %d: %s', $line, $snippet),
            $message,
        );
    }

    /**
     * @return list<string>
     */
    private function findUnprocessedDirectives(string $compiled): array
    {
        preg_match_all(self::UNPROCESSED_DIRECTIVE_PATTERN, $compiled, $matches);

        return array_values(array_unique($matches[0]));
    }

    /**
     * Lints the compiled PHP with `php -l` fed through stdin.
     */
    private function checkPhpSyntax(string $code): ?string
    {
        $process = proc_open(
            [PHP_BINARY, '-l'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        if (!is_resource($process)) {
            return null;
        }

        fwrite($pipes[0], $code);
        fclose($pipes[0]);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if (proc_close($process) === 0) {
            return null;
        }

        $message = trim($stderr !== '' ? $stderr : $stdout);
        $message = strtok($message, "\n");
        $message = $message === false ? 'PHP syntax error in compiled template.' : $message;

        return str_replace(' in Standard input code', ' in compiled template', $message);
    }
}
