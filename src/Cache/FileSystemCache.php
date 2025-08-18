<?php

declare(strict_types=1);

namespace Millancore\Pesto\Cache;

use Millancore\Pesto\Contract\Cache;
use Millancore\Pesto\Contract\Loader;

readonly class FileSystemCache implements Cache
{
    public function __construct(
        private string $cacheDir,
        private Loader $loader,
    ) {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function getCompiledPath(string $name): string
    {
        $hash = hash('xxh128', $name);

        return $this->cacheDir.DIRECTORY_SEPARATOR.$hash.'.php';
    }

    public function write(string $path, string $content): void
    {
        file_put_contents($path, $content);
    }

    public function isFresh(string $name): bool
    {
        $compiledPath = $this->getCompiledPath($name);
        $sourcePath = $this->loader->getPath($name);

        if (!file_exists($compiledPath) || $sourcePath === null) {
            return false;
        }

        return filemtime($compiledPath) >= filemtime($sourcePath);
    }
}
