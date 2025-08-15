<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\Compiler;
use Millancore\Pesto\Contract\Loader;

class PestoCompiler implements Compiler
{
    private Loader $loader;
    private SyntaxCompiler $syntaxCompiler;
    private DomCompiler $nodeCompiler;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        $this->syntaxCompiler = new SyntaxCompiler();

        $domPasses = [
            new Pass\PartialPass(),
            new Pass\IfPass(),
            new Pass\ForeachPass(),
            new Pass\ContextPass(),
            new Pass\UnwrapPass(),
            // ...
        ];

        $this->nodeCompiler = new DomCompiler($domPasses);
    }

    public function compile(string $source): string
    {
        $domProcessedSource = $this->nodeCompiler->compile($source);

        return $this->syntaxCompiler->compile($domProcessedSource);
    }
}
