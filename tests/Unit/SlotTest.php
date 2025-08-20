<?php

namespace Millancore\Pesto\Tests\Unit;

use Millancore\Pesto\Contract\Htmlable;
use Millancore\Pesto\Slot;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Slot::class)]
class SlotTest extends TestCase
{
    public function test_it_be_htmlable( ) : void
    {
        $slot = new Slot('slot-name');

        $this->assertInstanceOf(Htmlable::class, $slot);
        $this->assertEquals('slot-name', $slot->toHtml());
    }

}