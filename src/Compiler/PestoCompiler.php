<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\Compiler;

class PestoCompiler implements Compiler
{
    private SyntaxCompiler $syntaxCompiler;
    private DomCompiler $nodeCompiler;

    public function __construct()
    {
        $this->syntaxCompiler = new SyntaxCompiler();

        $domPasses = [
            new Pass\PartialPass(),
            new Pass\ForeachPass(),
            new Pass\IfPass(),
            new Pass\SlotPass(),
            new Pass\ContextPass(),
            new Pass\UnwrapPass(),
            // ...
        ];

        $this->nodeCompiler = new DomCompiler($domPasses);
    }

    public function compile(string $source): string
    {
        $source = $this->nodeCompiler->compile($source);

        return $source = $this->syntaxCompiler->compile($source);

        // return $this->nodeCompiler->compile($source);
    }
}
