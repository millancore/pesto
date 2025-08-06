<?php

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\SectionPass;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Tests\TestCase;

final class SectionPassTest extends TestCase
{
    private SectionPass $pass;

    public function setUp(): void
    {
        $this->pass = new SectionPass();
    }

    public function test_compile_a_simple_section(): void
    {
        $html = '<div php-section="test">Hello</div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $expected = '<?php $__pesto->startSection("test") ;?><div>Hello</div><?php $__pesto->stopSection();?>';

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_compile_multiple_sections(): void
    {
        $html = '<div php-section="header">Header</div><div php-section="footer">Footer</div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);
        $output = $pesto->getInnerXML('body');

        $this->assertStringContainsString('<?php $__pesto->startSection("header") ;?><div>Header</div><?php $__pesto->stopSection();?>', $output);
        $this->assertStringContainsString('<?php $__pesto->startSection("footer") ;?><div>Footer</div><?php $__pesto->stopSection();?>', $output);
    }

    public function test_does_nothing_if_no_section_attribute_is_present(): void
    {
        $html = '<div>No changes should be made.</div>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);
        $output = $pesto->getInnerXML('body');

        $this->assertEquals($html, $output);
    }

    public function test_removes_section_attribute_from_node(): void
    {
        $html = '<div php-section="test" id="element" class="bg-red">Hello</div>';

        $pesto = new Pesto($html);
        $this->pass->compile($pesto);

        $output = $pesto->getInnerXML('body');

        $this->assertStringNotContainsString('php-section', $output);
        $this->assertStringContainsString('id="element"', $output);
        $this->assertStringContainsString('class="bg-red"', $output);
    }


}