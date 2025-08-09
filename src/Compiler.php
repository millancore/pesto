<?php

namespace Millancore\Pesto;

use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\CompilerInterface;
use Millancore\Pesto\Contract\CompilerPassInterface;
use Millancore\Pesto\Contract\LoaderInterface;

class Compiler implements CompilerInterface
{
    /** @var CompilerInterface[] */
    protected array $passes = [];

    protected LoaderInterface $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;

        $this->init();
    }

    public function init() : void
    {
        $this->passes = [
            new Compiler\Pass\LayoutPass(),
            new Compiler\Pass\SlotPass(),
            new Compiler\Pass\YieldPass(),
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
        $pesto = new Pesto($source);

        /** @var CompilerPassInterface $pass */
        foreach ($this->passes as $pass) {
            $pass->compile($pesto);
        }

        return $pesto->getCompiledTemplate();
    }
}
