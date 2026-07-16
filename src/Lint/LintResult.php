<?php

declare(strict_types=1);

namespace Millancore\Pesto\Lint;

final readonly class LintResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public ?string $compiled,
        public array $errors,
    ) {
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
