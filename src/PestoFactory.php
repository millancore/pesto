<?php

declare(strict_types=1);

namespace Millancore\Pesto;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler\PestoCompiler;
use Millancore\Pesto\Contract\StackFilter;
use Millancore\Pesto\Filter\CoreFiltersStack;
use Millancore\Pesto\Filter\FilterRegister;
use Millancore\Pesto\Loader\FileSystemLoader;

class PestoFactory
{
    /**
     * @param array<StackFilter> $filterStack
     */
    public static function create(
        string $templatesPath,
        string $cachePath,
        array $filterStack = [],
    ): Environment {
        $loader = new FileSystemLoader($templatesPath);
        $cache = new FileSystemCache($cachePath, $loader);

        $renderer = new Renderer($loader, new PestoCompiler(), $cache);

        $filterRegister = new FilterRegister(array_merge(
            [new CoreFiltersStack()],
            $filterStack,
        ));

        return new Environment($renderer, $filterRegister);
    }
}
