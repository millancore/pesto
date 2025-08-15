<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\IfPass;
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
#[CoversClass(IfPass::class)]
final class IfPassTest extends TestCase
{
    private IfPass $pass;

    public function setUp(): void
    {
        $this->pass = new IfPass();
    }

    public function testCompilesASimpleIf(): void
    {
        $html = '<div php-if="$show">Hello</div>';
        $expected = '<?php if ($show): ?><div>Hello</div><?php endif; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompilesAnIfWithElse(): void
    {
        $html = '<div php-if="$show">Hello</div><div php-else>World</div>';
        $expected = '<?php if ($show): ?><div>Hello</div><?php else: ?><div>World</div><?php endif; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompilesAnNestedIf(): void
    {
        $html = '<div php-if="$show">Hello <div php-if="$getting">World</div></div>';
        $expected = '<?php if ($show): ?><div>Hello <?php if ($getting): ?><div>World</div><?php endif; ?></div><?php endif; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompilesAnIfWithElseif(): void
    {
        $html = '<div php-if="$show">Hello</div><div php-elseif="$getting">World</div>';
        $expected = '<?php if ($show): ?><div>Hello</div><?php elseif ($getting): ?><div>World</div><?php endif; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompilesAnIfWithElseifAndElse(): void
    {
        $html = '<div php-if="$show">Hello</div><div php-elseif="$getting">World</div><div php-else>Universe</div>';
        $expected = '<?php if ($show): ?><div>Hello</div><?php elseif ($getting): ?><div>World</div><?php else: ?><div>Universe</div><?php endif; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function testCompilesAnIfWithTemplateElseif(): void
    {
        $html = '<div php-if="$show">Hello</div><template php-elseif="true">World</template>';
        $expected = '<?php if ($show): ?><div>Hello</div><?php elseif (true): ?><template php-inner="">World</template><?php endif; ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
