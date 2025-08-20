<?php

namespace Millancore\Pesto\Tests\Unit;

use Millancore\Pesto\Contract\Cache;
use Millancore\Pesto\Contract\Compiler;
use Millancore\Pesto\Contract\Loader;
use Millancore\Pesto\Environment;
use Millancore\Pesto\Exception\CompilerException;
use Millancore\Pesto\Filter\FilterRegister;
use Millancore\Pesto\Renderer;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(Renderer::class)]
class RendererTest extends TestCase
{
    private Loader $loader;
    private Compiler $compiler;
    private Cache $cache;
    private Environment $env;
    private Renderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(Loader::class);
        $this->compiler = $this->createMock(Compiler::class);
        $this->cache = $this->createMock(Cache::class);
        $this->renderer = new Renderer($this->loader, $this->compiler, $this->cache);

        $this->env = new Environment($this->renderer, new FilterRegister);

    }

    public function test_render_when_cache_is_stale(): void
    {
       $this->markTestIncomplete();
    }

    public function test_render_when_cache_is_fresh(): void
    {
       $this->markTestIncomplete();
    }

    public function test_circular_dependency_detection(): void
    {
        $this->markTestIncomplete();
    }
}
