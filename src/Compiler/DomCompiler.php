<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\Compiler;
use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Pesto;

class DomCompiler implements Compiler
{
    /** @var CompilerPass[] */
    private array $passes;

    /**
     * @param array<CompilerPass> $passes
     */
    public function __construct(array $passes)
    {
        $this->passes = $passes;
    }

    public function compile(string $source): string
    {
        $pesto = new Pesto($source);

        foreach ($this->passes as $pass) {
            $pass->compile($pesto);
        }

        return $pesto->getCompiledTemplate();
    }
}
