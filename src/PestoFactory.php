<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler\PestoCompiler;
use Millancore\Pesto\Loader\FileSystemLoader;

class PestoFactory
{
    public static function create(string $templatesPath, string $cachePath): Environment
    {
        $loader = new FileSystemLoader($templatesPath);
        $compiler = new PestoCompiler($loader);
        $cache = new FileSystemCache($cachePath, $loader);

        return new Environment($loader, $compiler, $cache);
    }
}
