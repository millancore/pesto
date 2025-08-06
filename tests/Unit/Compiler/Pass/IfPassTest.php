<?php

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\IfPass;
use Millancore\Pesto\Compiler\Pesto;
use PHPUnit\Framework\TestCase;

final class IfPassTest extends TestCase
{
    private IfPass $pass;

    public function setUp(): void
    {
        $this->pass = new IfPass();
    }

    public function test_compiles_a_simple_if(): void
    {
        $html = '<div php-if="$show">Hello</div>';

        $expected = '<?php if ($show): ?><div>Hello</div><?php endif; ?>';

        $pesto = new Pesto($html);
        $this->pass->compile($pesto);

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_compiles_an_if_with_else(): void
    {
        $html = '<div php-if="$show">Hello</div><div php-else>World</div>';

        $expected = '<?php if ($show): ?><div>Hello</div><?php else: ?><div>World</div><?php endif; ?>';

        $pesto = new Pesto($html);
        $this->pass->compile($pesto);

        $this->assertEquals($expected, $pesto->getInnerXML('body'));

    }

    public function test_compiles_an_nested_if(): void
    {

        $html = '<div php-if="$show">Hello <div php-if="$getting">World</div></div>';
        $expected = '<?php if ($show): ?><div>Hello <?php if ($getting): ?><div>World</div><?php endif; ?></div><?php endif; ?>';

        $pesto = new Pesto($html);

        $this->pass->compile($pesto);

        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

}