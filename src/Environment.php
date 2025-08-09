<?php

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\CacheInterface;
use Millancore\Pesto\Contract\CompilerInterface;
use Millancore\Pesto\Contract\LoaderInterface;

class Environment
{
    protected array $sectionStack = [];
    protected array $sections = [];
    protected array $renderStack = [];

    public function __construct(
        private LoaderInterface   $loader,
        private CompilerInterface $compiler,
        private CacheInterface    $cache
    )
    {
    }

    public function make(string $name, array $data = []): View
    {
        return new View($this, $name, $data);
    }

    public function render(string $name, array $data = [])
    {
        if (in_array($name, $this->renderStack)) {
            throw new \Exception("Circular template dependency detected: " . implode(' -> ', $this->renderStack) . " -> $name");
        }

        $this->renderStack[] = $name;

        $compiledPath = $this->cache->getCompiledPath($name);

        if (!$this->cache->isFresh($name)) {
            $source = $this->loader->getSource($name);
            $compiledCode = $this->compiler->compile($source);
            $this->cache->write($compiledPath, $compiledCode);
        }

        ob_start();

        $this->renderTemplate($compiledPath, $data);

        array_pop($this->renderStack);

        $content  = ltrim(ob_get_clean());

        echo $content;
    }

    private function renderTemplate(string $path, array $data)
    {
        $__pesto = $this;
        $__data = $data;
        $__path = $path;

        return (static function () use ($__path, $__pesto, $__data) {
            extract($__data, EXTR_SKIP);

            return require $__path;
        })();
    }

    public function startSlot(string $name): void
    {
        if (ob_start()) {
            $this->sectionStack[] = $name;
        }
    }


    public function stopSlot()
    {
        if (empty($this->sectionStack)) {
            throw new \Exception('No section started');
        }

        $sectionName = array_pop($this->sectionStack);

        $content = ob_get_clean();

        if(!array_key_exists($sectionName, $this->sections)) {
            $this->sections[$sectionName] =  $content;
        }

        echo $content;
    }

    public function yield(string $name): void
    {
        echo $this->sections[$name] ?? '';
    }
}