<?php

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\CompilerInterface;

class SyntaxCompiler implements CompilerInterface
{
    private const ESCAPED_PATTERN = '/\{\{(.*?)\}\}/s';
    private const UNESCAPED_PATTERN = '/\{!!(.*?)!!\}/s';

    private const SLOT_DEFAULT_NAME = '__default';
    private const SLOT_VARIABLE = '$slot';

    public function compile(string $source): string
    {
        // First pass: handle unescaped expressions {!! !!}
        $source = $this->compileUnescapedExpressions($source);

        // Second pass: handle escaped expressions {{ }}
        $source = $this->compileEscapedExpressions($source);

        return $source;
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
        $expression = trim($expression);
        return "<?= {$expression} ?? \"\" ?>";
    }

    private function handleEscapedExpression(string $expression): string
    {
        $expression = trim($expression);

        if ($this->isDefaultSlot($expression)) {
            return $this->compileDefaultSlot();
        }

        if ($this->isNamedSlot($expression, $slotName)) {
            return $this->compileNamedSlot($slotName);
        }

        return $this->compileEscapedVariable($expression);
    }

    private function isDefaultSlot(string $expression): bool
    {
        return $expression === self::SLOT_VARIABLE;
    }

    private function isNamedSlot(string $expression, ?string &$slotName = null): bool
    {
        if (preg_match('/^slot\((["\'])(.*?)\1\)$/', $expression, $matches)) {
            $slotName = $matches[2];
            return true;
        }

        return false;
    }

    private function compileDefaultSlot(): string
    {
        return "<?= \$slots['" . self::SLOT_DEFAULT_NAME . "'] ?? \"\" ?>";
    }

    private function compileNamedSlot(string $slotName): string
    {
        return "<?= \$slots['{$slotName}'] ?? \"\" ?>";
    }

    private function compileEscapedVariable(string $expression): string
    {
        return "<?= \$__pesto->escape($expression) ?>";
    }
}