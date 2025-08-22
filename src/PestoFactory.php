<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler\PestoCompiler;
use Millancore\Pesto\Filter\CoreFilters;
use Millancore\Pesto\Filter\FilterRegistry;
use Millancore\Pesto\Filter\StringFilters;
use Millancore\Pesto\Loader\FileSystemLoader;

class PestoFactory
{
    public static function create(
        string $templatesPath,
        string $cachePath,
        array $filterStack = [],
    ): Environment {
        $loader = new FileSystemLoader($templatesPath);
        $cache = new FileSystemCache($cachePath, $loader);

        $renderer = new Renderer($loader, new PestoCompiler(), $cache);

        $providers = array_merge([new CoreFilters(), new StringFilters()], $filterStack);

        $filterRegistry = new FilterRegistry($providers);

        return new Environment($renderer, $filterRegistry);
    }
}
