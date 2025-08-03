<?php

namespace Millancore\Pesto;

use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerInterface;

class Compiler implements CompilerInterface
{
    /** @var CompilerInterface[] */
    protected array $passes = [];

    public function __construct()
    {
        $this->passes = [
            /*new Compiler\IncludePass(),
            new Compiler\StackPushPass($this),
            new Compiler\PrefixedAttributePass(),
            new Compiler\ForeachPass(), */
            new Compiler\Pass\IfPass(),
            /*new Compiler\TextPass(),
            new Compiler\HtmlPass(),
            new Compiler\StackRenderPass(), */
        ];
    }

    public array $stacks = [];

    public function compile(string $source): string
    {
        $pestoCrawler = new Pesto($source);

        foreach ($this->passes as $pass) {
            $pass->compile($pestoCrawler);
        }

        return $pestoCrawler->getCompiledTemplate();
    }
}
