<?php

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\LayoutPass;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Exception\CompilerException;
use Millancore\Pesto\Tests\TestCase;

final class LayoutPassTest extends TestCase
{
    private LayoutPass $pass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pass = new LayoutPass();
    }

    public function test_compiles_a_simple_layout(): void
    {
        $html = '<fragment php-layout="layout.php"><div>Hello</div></fragment>';

        $pesto = new Pesto($html);
        $this->pass->compile($pesto);

        $expected = '<?php $__pesto->startSlot(\'__default\'); ?><div>Hello</div><?php $__pesto->stopSlot(); $__pesto->render(\'layout.php\', get_defined_vars()); ?>';

        $this->assertEquals($expected, $pesto->getCompiledTemplate());
    }

    public function test_does_nothing_if_no_layout_attribute_is_present(): void
    {
        $html = '<div>No changes should be made.</div>';

        $pesto = new Pesto($html);
        $this->pass->compile($pesto);

        $this->assertEquals($html, $pesto->getCompiledTemplate());
    }

    public function test_throws_when_multiple_layouts_present(): void
    {
        $this->expectException(CompilerException::class);

        $html = '<div php-layout="a"></div><div php-layout="b"></div>';

        $pesto = new Pesto($html);
        $this->pass->compile($pesto);
    }
}