<?php

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\CacheInterface;
use Millancore\Pesto\Contract\CompilerInterface;
use Millancore\Pesto\Contract\LoaderInterface;

class Environment
{
    public function __construct(
        private LoaderInterface $loader,
        private CompilerInterface $compiler,
        private CacheInterface $cache
    ) {}

    public function render(string $name, array $data = []): string
    {
        $compiledPath = $this->cache->getCompiledPath($name);

        if (!$this->cache->isFresh($name)) {
            $source = $this->loader->getSource($name);
            $compiledCode = $this->compiler->compile($source);
            $this->cache->write($compiledPath, $compiledCode);
        }

        return $this->renderTemplate($compiledPath, $data);
    }

    private function renderTemplate(string $path, array $data): string
    {
        extract($data);
        ob_start();
        require $path;
        return ob_get_clean();
    }

    public function renderPartial(string $name, array $data = []): string
    {
        $source = $this->loader->getSource($name);
        $compiledCode = $this->compiler->compile($source);

        extract($data);
        ob_start();
        eval('?>' . $compiledCode);
        return ob_get_clean();
    }

    public function renderStack(string $name): string
    {
        if (isset($this->compiler->stacks[$name])) {
            return implode("\n", $this->compiler->stacks[$name]);
        }
        return '';
    }

}