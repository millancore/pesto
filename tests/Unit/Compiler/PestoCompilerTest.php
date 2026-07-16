<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Compiler;

use Millancore\Pesto\Compiler\PestoCompiler;
use Millancore\Pesto\Exception\CompilerException;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PestoCompiler::class)]
class PestoCompilerTest extends TestCase
{
    public function test_compiles_valid_template(): void
    {
        $compiled = (new PestoCompiler())->compile('<li php-foreach="$items as $item">{{ $item }}</li>');

        $this->assertStringContainsString('<?php foreach($items as $item): ?>', $compiled);
    }

    public function test_throws_on_unclosed_expression(): void
    {
        $this->expectException(CompilerException::class);
        $this->expectExceptionMessage('Unclosed "{{" expression on line 2');

        (new PestoCompiler())->compile("<div>\n<p>{{ \$name }</p>\n</div>");
    }

    public function test_throws_on_orphan_else_directive(): void
    {
        $this->expectException(CompilerException::class);
        $this->expectExceptionMessage('Orphan "php-else" directive on <p>');

        (new PestoCompiler())->compile('<p php-if="$ok">Yes</p><span>x</span><p php-else>No</p>');
    }

    public function test_throws_on_unprocessed_with_directive(): void
    {
        $this->expectException(CompilerException::class);
        $this->expectExceptionMessage('Unprocessed "php-with" directive on <section>');

        (new PestoCompiler())->compile('<section php-with="[\'a\' => 1]">no partial</section>');
    }

    public function test_escaped_expressions_do_not_trigger_validation(): void
    {
        $compiled = (new PestoCompiler())->compile('<p>@{{ vueBinding }}</p>');

        $this->assertStringContainsString('{{ vueBinding }}', $compiled);
    }

    public function test_validation_can_be_disabled(): void
    {
        $compiler = new PestoCompiler(validate: false);

        $compiled = $compiler->compile('<p>{{ $name }</p><p php-else>orphan</p>');

        $this->assertStringContainsString('php-else', $compiled);
    }
}
