<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Contract\Cache;
use Millancore\Pesto\Contract\Compiler;
use Millancore\Pesto\Contract\Loader;
use Millancore\Pesto\Exception\CompilerException;

class Renderer
{
    /**
     * @var array<string>
     */
    protected array $renderStack = [];

    public function __construct(
        private readonly Loader $loader,
        private readonly Compiler $compiler,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @param array<mixed> $data
     *
     * @throws CompilerException
     * @throws \Throwable
     */
    public function render(Environment $env, string $name, array $data = []): string
    {
        if (in_array($name, $this->renderStack)) {
            throw new CompilerException('Circular template dependency detected: '.implode(' -> ', $this->renderStack)." -> $name");
        }

        $this->renderStack[] = $name;

        try {
            $compiledPath = $this->cache->getCompiledPath($name);

            if (!$this->cache->isFresh($name)) {
                $source = $this->loader->getSource($name);
                $compiledCode = $this->compiler->compile($source);
                $this->cache->write($compiledPath, $compiledCode);
            }

            $content = $this->executeTemplate($env, $compiledPath, $data);
        } finally {
            array_pop($this->renderStack);
        }

        return ltrim($content);
    }

    /**
     * Executes the template in an isolated scope and captures the output.
     *
     * @param array<mixed> $data
     *
     * @throws \Throwable
     */
    private function executeTemplate(Environment $env, string $path, array $data): string
    {
        $__pesto = $env;
        $__data = $data;
        $__path = $path;

        ob_start();

        try {
            (static function () use ($__path, $__pesto, $__data) {
                extract($__data, EXTR_SKIP);
                require $__path;
            })();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';
    }
}
