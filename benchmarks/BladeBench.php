<?php

declare(strict_types=1);

namespace Millancore\Pesto\Benchmarks;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
class BladeBench
{
    private Factory $blade;

    public function setUp(): void
    {
        $templatesPath = __DIR__ . '/templates/blade';
        $cachePath = __DIR__ . '/cache/blade';

        $filesystem = new Filesystem();
        $container = new Container();

        $compiler = new BladeCompiler($filesystem, $cachePath);

        $resolver = new EngineResolver();
        $resolver->register('blade', function () use ($compiler) {
            return new CompilerEngine($compiler);
        });

        $finder = new FileViewFinder($filesystem, [$templatesPath]);
        $dispatcher = new Dispatcher($container);

        $this->blade = new Factory($resolver, $finder, $dispatcher);
        $this->blade->setContainer($container);
    }

    #[Bench\Subject]
    #[Bench\Groups(['simple'])]
    public function benchSimple(): void
    {
        $this->blade->make('simple', DataProvider::simple())->render();
    }

    #[Bench\Subject]
    #[Bench\Groups(['loop'])]
    public function benchLoop(): void
    {
        $this->blade->make('loop', DataProvider::loop())->render();
    }

    #[Bench\Subject]
    #[Bench\Groups(['conditional'])]
    public function benchConditional(): void
    {
        $this->blade->make('conditional', DataProvider::conditional())->render();
    }

    #[Bench\Subject]
    #[Bench\Groups(['partial'])]
    public function benchPartial(): void
    {
        $this->blade->make('partial', DataProvider::partial())->render();
    }
}
