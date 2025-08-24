<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Loader;

use Millancore\Pesto\Exception\LoaderException;
use Millancore\Pesto\Loader\FileSystemLoader;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileSystemLoader::class)]
class FileSystemLoaderTest extends TestCase
{
    private FileSystemLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = new FileSystemLoader(self::TEMPLATE_PATH);
    }

    public function test_it_gets_the_source_of_a_template(): void
    {
        $this->createTemplate('test.pesto', 'Hello, {{ $name }}!');
        $source = $this->loader->getSource('test.pesto');
        $this->assertEquals('Hello, {{ $name }}!', $source);
    }

    public function test_it_throws_an_exception_when_template_not_found(): void
    {
        $this->expectException(LoaderException::class);
        $this->loader->getSource('non_existent_template.pesto');
    }

    public function test_it_gets_the_path_of_a_template(): void
    {
        $this->createTemplate('test.pesto', '');
        $path = $this->loader->getPath('test.pesto');
        $this->assertEquals(self::TEMPLATE_PATH.'/test.pesto', $path);
    }

    public function test_it_returns_null_when_getting_path_for_non_existent_template(): void
    {
        $path = $this->loader->getPath('non_existent_template.pesto');
        $this->assertNull($path);
    }

    public function test_it_checks_if_a_file_exists(): void
    {
        $this->createTemplate('test.pesto', '');
        $this->assertTrue($this->loader->isFile(self::TEMPLATE_PATH.'/test.pesto'));
        $this->assertFalse($this->loader->isFile(self::TEMPLATE_PATH.'/non_existent_template.pesto'));
    }

    private function createTemplate(string $name, string $content): void
    {
        file_put_contents(self::TEMPLATE_PATH.'/'.$name, $content);
    }

    protected function tearDown(): void
    {
        $this->cleanupTemporaryTemplate();
    }
}
