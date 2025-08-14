<?php

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler\PestoCompiler;
use Millancore\Pesto\Environment;
use Millancore\Pesto\Loader\FileSystemLoader;
use Millancore\Pesto\View;
use PHPUnit\Framework\TestCase;

final class RenderTest extends TestCase
{
    private Environment $environment;

    public function setUp(): void
    {
        $loader = new FileSystemLoader(__DIR__ . '/../fixtures/templates');
        $cache = new FileSystemCache(__DIR__ . '/../fixtures/cache', $loader);

        $compiler = new PestoCompiler($loader);

        $this->environment = new Environment($loader, $compiler, $cache);
    }

    public function test_render_template()
    {
        // delete cache folder files
        $cachePath = __DIR__ . '/../fixtures/cache';
        array_map('unlink', glob($cachePath . '/*'));

        /** @var View $render */
        ob_start();
        $this->environment->render('parent.php', [
            'showFooter' => false,
            'rawCondition' => true
        ]);

        $render = ob_get_clean();

        dump($render);
        file_put_contents(__DIR__ . '/../fixtures/cache/parent.php', $render);
    }

}