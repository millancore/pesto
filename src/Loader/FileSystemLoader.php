<?php

namespace Millancore\Pesto\Loader;

use Millancore\Pesto\Contract\LoaderInterface;
use Millancore\Pesto\Exception\LoaderException;

class FileSystemLoader implements LoaderInterface
{
    public function __construct(private string $templateDir) {}

    /**
     * @throws LoaderException
     */
    public function getSource(string $name): string
    {
        $path = $this->getPath($name);

        if ($path === null || !is_readable($path)) {
            throw new LoaderException(sprintf('Template "%s" not found or not readable.', $name));
        }
        return file_get_contents($path);
    }

    public function getPath(string $name): ?string
    {
        $path = $this->templateDir . '/' . $name;

        return file_exists($path) ? $path : null;
    }

}