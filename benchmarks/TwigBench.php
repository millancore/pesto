<?php

declare(strict_types=1);

namespace Millancore\Pesto\Benchmarks;

use PhpBench\Attributes as Bench;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[Bench\BeforeMethods('setUp')]
class TwigBench
{
    private Environment $twig;

    public function setUp(): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/templates/twig');
        $this->twig = new Environment($loader, [
            'cache' => __DIR__ . '/cache/twig',
            'auto_reload' => true,
        ]);
    }

    #[Bench\Subject]
    #[Bench\Groups(['simple'])]
    public function benchSimple(): void
    {
        $this->twig->render('simple.html.twig', DataProvider::simple());
    }

    #[Bench\Subject]
    #[Bench\Groups(['loop'])]
    public function benchLoop(): void
    {
        $this->twig->render('loop.html.twig', DataProvider::loop());
    }

    #[Bench\Subject]
    #[Bench\Groups(['conditional'])]
    public function benchConditional(): void
    {
        $this->twig->render('conditional.html.twig', DataProvider::conditional());
    }

    #[Bench\Subject]
    #[Bench\Groups(['partial'])]
    public function benchPartial(): void
    {
        $this->twig->render('partial.html.twig', DataProvider::partial());
    }
}
