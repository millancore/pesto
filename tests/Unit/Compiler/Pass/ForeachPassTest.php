<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\ForeachPass;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ForeachPass::class)]
class ForeachPassTest extends TestCase
{
    private ForeachPass $pass;

    public function setUp(): void
    {
        $this->pass = new ForeachPass();
    }

    public function testCompileASimpleForeach(): void
    {
        $html = '<div php-foreach="$items as $item">Hello</div>';
        $expected = '<?php foreach($items as $item): ?><div>Hello</div><?php endforeach; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileASimpleForeachWithKey(): void
    {
        $html = '<div php-foreach="$items as $key => $item">Hello</div>';
        $expected = '<?php foreach($items as $key => $item): ?><div>Hello</div><?php endforeach; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompileTemplateForeach(): void
    {
        $html = '<template php-foreach="$items as $item">World</template>';
        $expected = '<?php foreach($items as $item): ?><template php-inner="">World</template><?php endforeach; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
