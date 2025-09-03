<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\Compiler;

class SyntaxCompiler implements Compiler
{
    private const string PATTERN = '/(?<!@)\{\{(.*?)\}\}/s';
    private const string ESCAPED_PATTERN = '/@(\{\{.*?\}\})/s';

    public function compile(string $source): string
    {
        $source = $this->compileExpressions($source);

        return $this->compileEscapedExpressions($source);
    }

    private function compileExpressions(string $source): string
    {
        return preg_replace_callback(
            self::PATTERN,
            fn ($matches) => $this->handleExpression($matches[1]),
            $source,
        ) ?? $source;
    }

    private function compileEscapedExpressions(string $source): string
    {
        return preg_replace(self::ESCAPED_PATTERN, '$1', $source) ?? $source;
    }

    private function handleExpression(string $expression): string
    {
        $expression = html_entity_decode(trim($expression), ENT_QUOTES, 'UTF-8');

        $parts = explode('|', $expression);
        $variable = trim(array_shift($parts));

        $filters = array_map(
            fn ($filter) => $this->formatFilter(trim($filter)),
            array_filter($parts, fn ($part) => trim($part) !== ''),
        );

        $filtersArray = empty($filters) ? '[]' : '['.implode(', ', $filters).']';

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
