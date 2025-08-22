<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit;

use Dom\HTMLDocument;
use Millancore\Pesto\Dom\Document;
use Millancore\Pesto\Dom\NodeCollection;
use Millancore\Pesto\Pesto;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(NodeCollection::class)]
#[UsesClass(Document::class)]
#[CoversClass(Pesto::class)]
class PestoTest extends TestCase
{
    public function test_get_html_document(): void
    {
        $html = '<div>Content</div>';
        $pesto = new Pesto($html);

        $this->assertInstanceOf(HTMLDocument::class, $pesto->getDocument());
    }

    public function test_find_returns_node_collection(): void
    {
        $html = '<div class="test">Content</div><p class="test">More content</p>';
        $pesto = new Pesto($html);

        $result = $pesto->find('.test');

        $this->assertInstanceOf(NodeCollection::class, $result);
    }

    public function test_get_inner_xml_with_existing_element(): void
    {
        $html = '<div id="container"><p>Hello</p></div>';
        $pesto = new Pesto($html);

        $innerXml = $pesto->getInnerXML('#container');

        $this->assertStringContainsString('<p>Hello</p>', $innerXml);
    }

    public function test_get_inner_xml_with_non_existent_element(): void
    {
        $html = '<div>Content</div>';
        $pesto = new Pesto($html);

        $innerXml = $pesto->getInnerXML('#nonexistent');

        $this->assertEquals('', $innerXml);
    }

    public function test_get_compiled_template_preserves_php_tags(): void
    {
        $html = '<div><?php echo "test"; ?><?= $var ?></div>';
        $pesto = new Pesto($html);

        $compiled = $pesto->getCompiledTemplate();

        $this->assertStringContainsString('<?php echo "test"; ?>', $compiled);
        $this->assertStringContainsString('<?= $var ?>', $compiled);
    }

    public function test_get_compiled_template_with_fragment_html(): void
    {
        $html = '<p>Hello World</p>';
        $pesto = new Pesto($html);

        $compiled = $pesto->getCompiledTemplate();

        $this->assertStringContainsString('<p>Hello World</p>', $compiled);
        $this->assertStringNotContainsString('__pesto-template-wrapper__', $compiled);
    }

    public function test_get_compiled_template_with_full_html_document(): void
    {
        $html = '<html lang="en"><body><div>Content</div></body></html>';
        $pesto = new Pesto($html);

        $compiled = $pesto->getCompiledTemplate();

        $this->assertStringContainsString('<html lang="en">', $compiled);
        $this->assertStringContainsString('<body>', $compiled);
    }
}
