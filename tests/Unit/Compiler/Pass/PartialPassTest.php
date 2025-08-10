<?php

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\PartialPass;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Tests\TestCase;

class PartialPassTest extends TestCase
{
    private PartialPass $pass;

    public function setUp(): void
    {
        $this->pass = new PartialPass();
    }

    public function test_compile_a_simple_partial(): void
    {
        $html = '<div php-partial="test.php">Hello</div>';
        $expected = '<?php $__pesto->start("test.php", []); ?>Hello<?php $__pesto->end(); ?>';


        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $this->assertEquals($expected, $pesto->getCompiledTemplate());
    }


    public function test_compile_a_partial_with_variables(): void
    {
        $html = '<div php-partial="test.php" php-with=\'["id" => "test-123"]\'>Hello</div>';
        $expected = '<?php $__pesto->start("test.php", ["id" => "test-123"]); ?>Hello<?php $__pesto->end(); ?>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $this->assertEquals($expected, $pesto->getCompiledTemplate());
    }

    public function test_compile_nested_partials() : void
    {
        $html = '<ul php-partial="list.php"><li>parent item</li><li><ul php-partial="list.php"><li>Child Item</li></ul></li></ul>';
        $expected = '<?php $__pesto->start("list.php", []); ?><li>parent item</li><li><?php $__pesto->start("list.php", []); ?><li>Child Item</li><?php $__pesto->end(); ?></li><?php $__pesto->end(); ?>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $this->assertEquals($expected, $pesto->getCompiledTemplate());

    }


}