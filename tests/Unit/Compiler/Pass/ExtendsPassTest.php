<?php

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\ExtendsPass;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Tests\TestCase;

final class ExtendsPassTest extends TestCase
{
    private ExtendsPass $pass;

    public function setUp(): void
    {
        $this->pass = new ExtendsPass();
    }

    public function test_compile_a_simple_extends() : void
    {
        $html = '<div php-extends="layout">Hello</div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $expected = '<div>Hello</div><?php $__pesto->render("layout", get_defined_vars()); ?>';

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

}