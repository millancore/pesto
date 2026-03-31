<?php

declare(strict_types=1);

namespace Millancore\Pesto\Benchmarks;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use PhpBench\Attributes as Bench;

#[Bench\BeforeMethods('setUp')]
class PestoBench
{
    private Environment $env;
    private string $templatesPath;
    private string $cachePath;

    public function setUp(): void
    {
        $this->templatesPath = __DIR__ . '/templates/pesto';
        $this->cachePath = __DIR__ . '/cache/pesto';

        $this->env = PestoFactory::create($this->templatesPath, $this->cachePath);
    }

    #[Bench\Subject]
    #[Bench\Groups(['simple'])]
    public function benchSimple(): void
    {
        $this->env->make('simple.html', DataProvider::simple())->toHtml();
    }

    #[Bench\Subject]
    #[Bench\Groups(['loop'])]
    public function benchLoop(): void
    {
        $this->env->make('loop.html', DataProvider::loop())->toHtml();
    }

    #[Bench\Subject]
    #[Bench\Groups(['conditional'])]
    public function benchConditional(): void
    {
        $this->env->make('conditional.html', DataProvider::conditional())->toHtml();
    }

    #[Bench\Subject]
    #[Bench\Groups(['partial'])]
    public function benchPartial(): void
    {
        $this->env->make('partial.html', DataProvider::partial())->toHtml();
    }
}
