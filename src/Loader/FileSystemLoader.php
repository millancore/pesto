<?php

declare(strict_types=1);

namespace Millancore\Pesto\Loader;

use Millancore\Pesto\Contract\Loader;
use Millancore\Pesto\Exception\LoaderException;

class FileSystemLoader implements Loader
{
    public function __construct(
        private string $templateDir
    ){ }

    public function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * @throws LoaderException
     */
    public function getSource(string $name): string
    {
        $path = $this->getPath($name);

        if ($path === null || !is_readable($path)) {
            throw new LoaderException(sprintf('Template "%s" not found or not readable.', $name));
        }

        $source = file_get_contents($path);

        return $source === false ? '' : $source;
    }

    public function getPath(string $name): ?string
    {
        $path = $this->templateDir.'/'.$name;

        return file_exists($path) ? $path : null;
    }
}
