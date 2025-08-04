<?php

namespace Millancore\Pesto\Tests\Unit\Compiler;

use Millancore\Pesto\Compiler\Pass\ExtendsPass;
use Millancore\Pesto\Compiler\Pesto;
use Millancore\Pesto\Contract\LoaderInterface;
use Millancore\Pesto\Exception\CompilerException;
use PHPUnit\Framework\TestCase;

final class ExtendsPassTest extends TestCase
{
    private LoaderInterface $loader;

    public function setUp(): void
    {
        $this->loader = $this->createMock(LoaderInterface::class);
    }

    public function test_it_compiles_a_simple_extends_and_yield(): void
    {
        $parent = '<html lang="en"><body><h1 php-yield="title">Default Title</h1></body></html>';
        $child = '<div php-extends="parent.html"><h1 php-section="title">Page Title</h1></div>';

        $this->loader->method('getSource')
            ->with('parent.html')
            ->willReturn($parent);

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($child);

        $pass->compile($pesto);

        $expected = '<h1>Page Title</h1>';
        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_it_handles_default_yield_content_when_section_is_not_provided(): void
    {
        $parent = '<html lang="en"><body><h1 php-yield="title">Default Title</h1><div php-yield="content"><p>Default Content</p></div></body></html>';
        $child = '<div php-extends="parent.html"><h1 php-section="title">Page Title</h1></div>';

        $this->loader->method('getSource')
            ->with('parent.html')
            ->willReturn($parent);

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($child);

        $pass->compile($pesto);

        $expected = '<h1>Page Title</h1><div><p>Default Content</p></div>';
        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_it_compiles_multiple_sections(): void
    {
        $parent = '<html lang="en"><body><h1 php-yield="title"></h1><div php-yield="content"></div></body></html>';
        $child = '<div php-extends="parent.html"><h1 php-section="title">Page Title</h1><div php-section="content"><p>Page Content</p></div></div>';

        $this->loader->method('getSource')
            ->with('parent.html')
            ->willReturn($parent);

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($child);
        $pass->compile($pesto);

        $expected = '<h1>Page Title</h1><div><p>Page Content</p></div>';
        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_it_handles_multiple_nodes_in_one_section(): void
    {
        $parent = '<html lang="en"><body><div php-yield="content"></div></body></html>';
        $child = '<div php-extends="parent.html"><div php-section="content"><h2>Subtitle</h2><p>Paragraph.</p></div></div>';

        $this->loader->method('getSource')
            ->with('parent.html')
            ->willReturn($parent);

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($child);
        $pass->compile($pesto);

        $expected = '<div><h2>Subtitle</h2><p>Paragraph.</p></div>';
        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_it_handles_multiple_section_tags_for_same_yield(): void
    {
        $parent = '<html lang="en"><body><div php-yield="content"></div></body></html>';
        $child = '<div php-extends="parent.html"><p php-section="content">First line.</p><p php-section="content">Second line.</p></div>';

        $this->loader->method('getSource')
            ->with('parent.html')
            ->willReturn($parent);

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($child);
        $pass->compile($pesto);

        $expected = '<p>First line.</p><p>Second line.</p>';
        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }

    public function test_it_does_nothing_if_no_extends_attribute_is_present(): void
    {
        $html = '<html lang="en"><body><p>No changes should be made.</p></body></html>';
        
        $this->loader->expects($this->never())->method('getSource');

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($html);
        $pass->compile($pesto);

        $expected = '<p>No changes should be made.</p>';
        $this->assertEquals($expected, $pesto->getInnerXML('body'));
    }
    
    public function test_it_trigger_exception_if_has_multiple_extends_attributes(): void
    {
        $this->expectException(CompilerException::class);
        $this->expectExceptionMessage('Only one [php-extends] tag is allowed per template.');

        $html = '<div php-extends="parent.html"></div><div php-extends="another.html"></div>';

        $pass = new ExtendsPass($this->loader);
        $pesto = new Pesto($html);
        $pass->compile($pesto);
    }


}