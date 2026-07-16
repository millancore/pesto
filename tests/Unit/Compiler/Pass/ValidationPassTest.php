<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler\Pass;

use Millancore\Pesto\Compiler\Pass\ValidationPass;
use Millancore\Pesto\Exception\CompilerException;
use Millancore\Pesto\Pesto;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ValidationPass::class)]
class ValidationPassTest extends TestCase
{
    public function test_passes_clean_template(): void
    {
        $pesto = new Pesto('<p class="ok">no directives left</p>');

        (new ValidationPass())->compile($pesto);

        $this->assertStringContainsString('no directives left', $pesto->getCompiledTemplate());
    }

    public function test_throws_listing_every_leftover_directive(): void
    {
        $pesto = new Pesto('<p p:elseif="$a">a</p><section php-with="[1]">b</section>');

        try {
            (new ValidationPass())->compile($pesto);
            $this->fail('Expected CompilerException was not thrown.');
        } catch (CompilerException $e) {
            $this->assertStringContainsString('Orphan "p:elseif" directive on <p>', $e->getMessage());
            $this->assertStringContainsString('Unprocessed "php-with" directive on <section>', $e->getMessage());
        }
    }
}
