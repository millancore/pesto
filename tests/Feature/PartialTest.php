<?php

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler;
use Millancore\Pesto\Environment;
use Millancore\Pesto\Loader\FileSystemLoader;
use Millancore\Pesto\Tests\TestCase;

class PartialTest extends TestCase
{
    private Environment $environment;

    public function setUp(): void
    {
        $loader = new FileSystemLoader(__DIR__ . '/../fixtures/templates');
        $cache = new FileSystemCache(__DIR__ . '/../fixtures/cache', $loader);
        $compiler = new Compiler($loader);
        $this->environment = new Environment($loader, $compiler, $cache);
    }

    public function test_render_partial()
    {
        $cachePath = __DIR__ . '/../fixtures/cache';
        //array_map('unlink', glob($cachePath . '/*'));

        ob_start();
        $this->environment->render('composition-list.php');
        $content = ob_get_clean();

        file_put_contents(__DIR__ . '/../fixtures/cache/composition-list.php', $content);
        dd($content);
    }
}