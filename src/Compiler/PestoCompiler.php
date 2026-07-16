<?php

declare(strict_types=1);

namespace Millancore\Pesto\Compiler;

use Millancore\Pesto\Contract\Compiler;
use Millancore\Pesto\Exception\CompilerException;

class PestoCompiler implements Compiler
{
    private SyntaxCompiler $syntaxCompiler;
    private DomCompiler $nodeCompiler;

    /**
     * @param bool $validate reject templates with unclosed "{{" expressions
     *                       or directives no pass consumed
     */
    public function __construct(
        private readonly bool $validate = true,
    ) {
        $this->syntaxCompiler = new SyntaxCompiler();

        $domPasses = [
            new Pass\PartialPass(),
            new Pass\ForeachPass(),
            new Pass\IfPass(),
            new Pass\SlotPass(),
            new Pass\ContextPass(),
            new Pass\UnwrapPass(),
        ];

        if ($this->validate) {
            $domPasses[] = new Pass\ValidationPass();
        }

        $this->nodeCompiler = new DomCompiler($domPasses);
    }

    /**
     * @throws CompilerException
     */
    public function compile(string $source): string
    {
        if ($this->validate) {
            $this->assertExpressionsAreClosed($source);
        }

        $source = $this->nodeCompiler->compile($source);

        return $this->syntaxCompiler->compile($source);
    }

    /**
     * A "{{" without a following "}}" never gets closed. Pairs are consumed
     * left to right, mirroring the syntax compiler's non-greedy matching.
     *
     * @throws CompilerException
     */
    private function assertExpressionsAreClosed(string $source): void
    {
        $pos = 0;

        while (($start = strpos($source, '{{', $pos)) !== false) {
            $end = strpos($source, '}}', $start + 2);

            if ($end === false) {
                $line = $start === 0 ? 1 : substr_count($source, "\n", 0, $start) + 1;

                throw new CompilerException(sprintf('Unclosed "{{" expression on line %d: missing matching "}}".', $line));
            }

            $pos = $end + 2;
        }
    }
}
