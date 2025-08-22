<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit\Cache;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Contract\Loader;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileSystemCache::class)]
class FileSystemCacheTest extends TestCase
{
    private Loader $loader;
    private FileSystemCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->createMock(Loader::class);
        $this->cache = new FileSystemCache(self::CACHE_PATH, $this->loader);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Clean up created cache files
        $files = glob(self::CACHE_PATH.'/*.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function testItCreatesCacheDirectoryIfNotExists(): void
    {
        $tempCacheDir = sys_get_temp_dir().'/pesto_test_cache';
        if (is_dir($tempCacheDir)) {
            rmdir($tempCacheDir);
        }
        $this->assertDirectoryDoesNotExist($tempCacheDir);

        new FileSystemCache($tempCacheDir, $this->loader);

        $this->assertDirectoryExists($tempCacheDir);
        rmdir($tempCacheDir);
    }

    public function testGetCompiledPathReturnsCorrectPath(): void
    {
        $name = 'template.html';
        $hash = hash('xxh128', $name);
        $expectedPath = self::CACHE_PATH.DIRECTORY_SEPARATOR.$hash.'.php';

        $this->assertSame($expectedPath, $this->cache->getCompiledPath($name));
    }

    public function testWriteCreatesFileWithContent(): void
    {
        $path = self::CACHE_PATH.'/test.php';
        $content = '<?php echo "Hello World";';

        $this->cache->write($path, $content);

        $this->assertFileExists($path);
        $this->assertSame($content, file_get_contents($path));
    }

    public function testIsFreshReturnsFalseIfCompiledFileDoesNotExist(): void
    {
        $this->loader->method('getPath')->willReturn(self::TEMPLATE_PATH.'/template.html');
        $this->assertFalse($this->cache->isFresh('template.html'));
    }

    public function testIsFreshReturnsFalseIfSourceIsNewer(): void
    {
        $templateName = 'newer_template.html';
        $sourcePath = self::TEMPLATE_PATH.'/'.$templateName;
        $compiledPath = $this->cache->getCompiledPath($templateName);

        file_put_contents($compiledPath, 'cached content');
        touch($compiledPath, time() - 3600);

        file_put_contents($sourcePath, 'source content');

        $this->loader->method('getPath')->willReturn($sourcePath);

        $this->assertFalse($this->cache->isFresh($templateName));

        unlink($sourcePath);
    }

    public function testIsFreshReturnsTrueIfCacheIsNewer(): void
    {
        $templateName = 'older_template.html';
        $sourcePath = self::TEMPLATE_PATH.'/'.$templateName;
        $compiledPath = $this->cache->getCompiledPath($templateName);

        file_put_contents($sourcePath, 'source content');
        touch($sourcePath, time() - 3600);

        file_put_contents($compiledPath, 'cached content');

        $this->loader->method('getPath')->willReturn($sourcePath);

        $this->assertTrue($this->cache->isFresh($templateName));

        unlink($sourcePath);
    }

    public function testIsFreshReturnsFalseIfSourcePathIsNull(): void
    {
        $this->loader->method('getPath')->willReturn(null);
        $this->assertFalse($this->cache->isFresh('non_existent_template.html'));
    }
}
