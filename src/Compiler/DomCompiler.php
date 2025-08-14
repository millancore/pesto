<?php

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\Compiler;
use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Pesto;

class DomCompiler implements Compiler
{
    /** @var CompilerPass[] */
    private array $passes;

    public function __construct(array $passes)
    {
        $this->passes = $passes;
    }

    public function compile(string $source): string
    {
        // TODO: move to constructor?
        $pesto = new Pesto($source);

        foreach ($this->passes as $pass) {
            $pass->compile($pesto);
        }

        return $pesto->getCompiledTemplate();
    }
}