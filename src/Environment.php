<?php

namespace Millancore\Pesto;

use Exception;
use Millancore\Pesto\Contract\CacheInterface;
use Millancore\Pesto\Contract\CompilerInterface;
use Millancore\Pesto\Contract\LoaderInterface;
use Millancore\Pesto\Filter\CoreFiltersStack;
use Millancore\Pesto\Filter\FilterRegister;

class Environment
{
    protected array $sectionStack = [];
    protected array $renderStack = [];

    protected FilterRegister $filterRegister;

    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly CompilerInterface $compiler,
        private readonly CacheInterface $cache
    )
    {
        $this->filterRegister = new FilterRegister([
            new CoreFiltersStack()
        ]);
    }


    /**
     * @throws Exception
     */
    public function render(string $name, array $data = []): void
    {
        if (in_array($name, $this->renderStack)) {
            throw new Exception("Circular template dependency detected: " . implode(' -> ', $this->renderStack) . " -> $name");
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

    private function renderTemplate(string $path, array $data): void
    {
        $__pesto = $this;
        $__data = $data;
        $__path = $path;

        (static function () use ($__path, $__pesto, $__data) {
            extract($__data, EXTR_SKIP);

            return require $__path;
        })();
    }


    public function start(string $name, array $data = []) : void
    {
        if (ob_start()) {
            $this->sectionStack[] = [
                'name' => $name,
                'data' => $data
            ];
        }

    }

    /**
     * @throws Exception
     */
    public function end() : void
    {
        $content = ob_get_clean();

        $partial = array_pop($this->sectionStack);

        $partial['data']['slot'] = $content;

        $this->render($partial['name'], $partial['data']);
    }


    public function output($expression, $filters = [])
    {
        foreach ($filters as $filter) {
            $expression = $this->filterRegister->apply($expression, $filter);
        }

        return $expression;
    }
}