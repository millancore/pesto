<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\PartialPass;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PartialPass::class)]
class PartialPassTest extends TestCase
{
    private PartialPass $pass;

    public function setUp(): void
    {
        $this->pass = new PartialPass();
    }

    public function testCompileASimplePartial(): void
    {
        $html = '<div php-partial="test.php">Hello</div>';
        $expected = '<?php $__pesto->start("test.php", []); ?><div>Hello</div><?php $__pesto->end(); ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileAPartialWithVariables(): void
    {
        $html = '<div php-partial="test.php" php-with=\'["id" => "test-123"]\'>Hello</div>';
        $expected = '<?php $__pesto->start("test.php", ["id" => "test-123"]); ?><div>Hello</div><?php $__pesto->end(); ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileNestedPartials(): void
    {
        $html = '<ul php-partial="list.php"><li>parent item</li><li><ul php-partial="list.php"><li>Child Item</li></ul></li></ul>';
        $expected = '<?php $__pesto->start("list.php", []); ?><ul><li>parent item</li><li><?php $__pesto->start("list.php", []); ?><ul><li>Child Item</li></ul><?php $__pesto->end(); ?></li></ul><?php $__pesto->end(); ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
