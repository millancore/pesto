<?php

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Tests\TestCase;
use Millancore\Pesto\Compiler\Pass\YieldPass;

final class YieldPassTest extends TestCase
{
    private YieldPass $pass;

    public function setUp(): void
    {
        $this->pass = new YieldPass();
    }

    public function test_compile_a_simple_yield(): void
    {
        $html = '<div php-yield="content"></div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $expected = '<?php $__pesto->yield("content");?>';

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_compile_multiple_yields(): void
    {
        $html = '<div php-yield="header"></div><div php-yield="footer"></div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);
        $output = $pesto->getInnerXML('body');

        $this->assertStringContainsString('<?php $__pesto->yield("header");?>', $output);
        $this->assertStringContainsString('<?php $__pesto->yield("footer");?>', $output);
    }

    public function test_does_nothing_if_no_yield_attribute_is_present(): void
    {
        $html = '<div>No changes should be made.</div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);
        $output = $pesto->getInnerXML('body');

        $this->assertEquals($html, $output);
    }

    public function test_removes_php_yield_attribute(): void
    {
        $html = '<section php-yield="main-content" class="container" id="main"></section>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);
        $output = $pesto->getInnerXML('body');


        $this->assertStringNotContainsString('php-yield', $output);
        $this->assertStringContainsString('$__pesto->yield("main-content");', $output);
    }

    public function test_compile_nested_yield(): void
    {
        $html = '<div><span php-yield="nested"></span></div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);
        $output = $pesto->getInnerXML('body');

        $this->assertEquals('<div><?php $__pesto->yield("nested");?></div>', $output);
    }

    public function test_compile_yield_with_complex_selector_name(): void
    {
        $html = '<div php-yield="main-sidebar-content"></div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $expected = '<?php $__pesto->yield("main-sidebar-content");?>';

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_ignores_content_inside_yield_element(): void
    {
        $html = '<div php-yield="content">This content will be ignored</div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $expected = '<?php $__pesto->yield("content");?>';

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }
}
