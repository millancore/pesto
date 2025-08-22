<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\ForeachPass;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Dom\NodeCollection;
use Millancore\Pesto\Pesto;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Document::class)]
#[UsesClass(Node::class)]
#[UsesClass(NodeCollection::class)]
#[UsesClass(Pesto::class)]
#[CoversClass(ForeachPass::class)]
class ForeachPassTest extends TestCase
{
    private ForeachPass $pass;

    public function setUp(): void
    {
        $this->pass = new ForeachPass();
    }

    public function test_compile_a_simple_foreach(): void
    {
        $html = '<div php-foreach="$items as $item">Hello</div>';
        $expected = '<?php foreach($items as $item): ?><div>Hello</div><?php endforeach; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_a_simple_foreach_with_key(): void
    {
        $html = '<div php-foreach="$items as $key => $item">Hello</div>';
        $expected = '<?php foreach($items as $key => $item): ?><div>Hello</div><?php endforeach; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compile_template_foreach(): void
    {
        $html = '<template php-foreach="$items as $item">World</template>';
        $expected = '<?php foreach($items as $item): ?><template php-inner="">World</template><?php endforeach; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
