<?php

namespace Millancore\Pesto;

use Millancore\Pesto\Compiler\SyntaxCompiler;
use Millancore\Pesto\Compiler\NodeCompiler;
use Millancore\Pesto\Contract\LoaderInterface;
use Millancore\Pesto\Contract\CompilerInterface;

class Compiler implements CompilerInterface
{
    private LoaderInterface $loader;
    private SyntaxCompiler $syntaxCompiler;
    private NodeCompiler $nodeCompiler;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
        $this->syntaxCompiler = new SyntaxCompiler();

        $domPasses = [
            new Compiler\Pass\PartialPass(),
            new Compiler\Pass\IfPass(),
            new Compiler\Pass\ContextPass(),
            new Compiler\Pass\UnwrapPass()
            //...
        ];

        $this->nodeCompiler = new NodeCompiler($domPasses);
    }

    public function compile(string $source): string
    {
        $domProcessedSource = $this->nodeCompiler->compile($source);

        return $this->syntaxCompiler->compile($domProcessedSource);
    }
}