<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\SlotPass;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Dom\NodeCollection;
use Millancore\Pesto\Pesto;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(SlotPass::class)]
#[UsesClass(Document::class)]
#[UsesClass(Node::class)]
#[UsesClass(NodeCollection::class)]
#[UsesClass(Pesto::class)]
class SlotPassTest extends TestCase
{
    private SlotPass $pass;

    public function setUp(): void
    {
        $this->pass = new SlotPass();
    }

    public function test_compiled_named_slot(): void
    {
        $html = '<div php-slot="slot_name">Slot Content</div>';
        $expected = '<?php $__pesto->slot("slot_name"); ?><div>Slot Content</div><?php $__pesto->endSlot(); ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }

    public function test_compiled_named_slot_using_template(): void
    {
        $html = '<template php-slot="slot_name">Slot Content</template>';
        $expected = '<?php $__pesto->slot("slot_name"); ?><template php-inner="">Slot Content</template><?php $__pesto->endSlot(); ?>';

        $this->assertCompiledEquals($this->pass, $expected, $html);
    }
}
