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
        return $this->syntaxCompiler->compile(
            $this->nodeCompiler->compile($source),
        );
    }
}
