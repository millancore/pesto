<?php

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler;
use Millancore\Pesto\Environment;
use Millancore\Pesto\Loader\FileSystemLoader;
use PHPUnit\Framework\TestCase;

final class RenderTest extends TestCase
{
    private Environment $environment;

    public function setUp(): void
    {
        $loader = new FileSystemLoader(__DIR__ . '/../fixtures/templates');
        $cache = new FileSystemCache(__DIR__ . '/../fixtures/cache', $loader);

        $compiler = new Compiler($loader);

        $this->environment = new Environment($loader, $compiler, $cache);
    }

    public function test_render_template()
    {
        $cacheDir = __DIR__ . '/../fixtures/cache';

        array_map('unlink', glob($cacheDir . '/*'));

        $render = $this->environment->render('child.php', [
            'showFooter' => true,
            'rawCondition' => true
        ]);

        ob_start();
        echo $render;
        $output = ob_get_clean();


        $this->assertEquals(file_get_contents(__DIR__ . '/../fixtures/output.html'), $output);

    }

}