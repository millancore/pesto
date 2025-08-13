<?php

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\CompilerInterface;

class SyntaxCompiler implements CompilerInterface
{
    private const string ESCAPED_PATTERN = '/\{\{(.*?)\}\}/s';
    private const string UNESCAPED_PATTERN = '/\{!!(.*?)!!\}/s';

    public function compile(string $source): string
    {
        // First pass: handle unescaped expressions {!! !!}
        $source = $this->compileUnescapedExpressions($source);

        // Second pass: handle escaped expressions {{ }}
        return $this->compileEscapedExpressions($source);
    }

    private function compileUnescapedExpressions(string $source): string
    {
        return preg_replace_callback(
            self::UNESCAPED_PATTERN,
            fn($matches) => $this->handleUnescapedExpression($matches[1]),
            $source
        );
    }

    private function compileEscapedExpressions(string $source): string
    {
        return preg_replace_callback(
            self::ESCAPED_PATTERN,
            fn($matches) => $this->handleEscapedExpression($matches[1]),
            $source
        );
    }

    private function handleUnescapedExpression(string $expression): string
    {
        return "<?php echo trim($expression) ?>";
    }

    private function handleEscapedExpression(string $expression): string
    {
        $expression = trim($expression);

        $parts = explode('|', $expression);
        $variable = trim(array_shift($parts));

        $filters = array_map(
            fn($filter) => $this->formatFilter(trim($filter)),
            array_filter($parts, fn($part) => trim($part) !== '')
        );

        $filtersArray = empty($filters) ? '[]' : '[' . implode(', ', $filters) . ']';

        return "<?= \$__pesto->output($variable, $filtersArray) ?>";
    }

    private function formatFilter(string $filter): string
    {
        if (!str_contains($filter, ':')) {
            return "'$filter'";
        }

        [$name, $params] = explode(':', $filter, 2);
        $paramList = implode(', ', array_map('trim', explode(',', $params)));

        return "['$name', $paramList]";
    }
}