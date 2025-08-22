<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\UnwrapPass;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Dom\Node;
use Millancore\Pesto\Dom\NodeCollection;
use Millancore\Pesto\Pesto;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(UnwrapPass::class)]
#[UsesClass(Document::class)]
#[UsesClass(Node::class)]
#[UsesClass(NodeCollection::class)]
#[UsesClass(Pesto::class)]
class UnwrapPassTest extends TestCase
{
    private UnwrapPass $pass;

    public function setUp(): void
    {
        $this->pass = new UnwrapPass();
    }

    public function testUnwrapContentInnerAttribute(): void
    {
        $html = '<div php-inner>Hello</div>';

        $this->assertCompiledEquals($this->pass, 'Hello', $html);
    }
}
