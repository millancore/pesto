<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit;

use Millancore\Pesto\Slot;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Slot::class)]
class SlotTest extends TestCase
{
    public function test_get_slot_content(): void
    {
        $slot = new Slot('slot-name');

        $this->assertEquals('slot-name', $slot->content);
    }

    public function test_to_html() : void
    {
        $slot = new Slot('slot-name');

        $this->assertEquals('slot-name', $slot->toHtml());
    }
}
