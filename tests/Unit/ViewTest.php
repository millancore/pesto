<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit;

use Millancore\Pesto\Contract\Htmlable;
use Millancore\Pesto\Environment;
use Millancore\Pesto\Tests\TestCase;
use Millancore\Pesto\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        // mock environment
        $this->env = $this->createMock(Environment::class);
    }

    public function test_it_be_htmlable(): void
    {
        $this->env->method('render')->willReturn('content');

        $view = new View($this->env, 'test', []);

        $this->assertInstanceOf(Htmlable::class, $view);
        $this->assertEquals('content', $view->toHtml());
    }

    public function test_it_be_stringable(): void
    {
        $this->env->method('render')->willReturn('content');
        $view = new View($this->env, 'test', []);

        $this->assertEquals('content', (string) $view);
        $this->assertEquals('content', $view->__toString());
    }

    public function test_it_can_render_with_data(): void
    {
        $data = ['name' => 'John'];

        $this->env->method('render')->willReturn('content');
        $view = new View($this->env, 'test', $data);

        $this->assertEquals($data, $view->getData());
    }

    public function test_it_can_add_more_data(): void
    {
        $view = new View($this->env, 'test', []);

        $view->with(['name' => 'John']);

        $this->assertEquals(['name' => 'John'], $view->getData());
    }
}
