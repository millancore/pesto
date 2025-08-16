<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Unit;

use Millancore\Pesto\Cache\FileSystemCache;
use Millancore\Pesto\Compiler\DomCompiler;
use Millancore\Pesto\Compiler\PestoCompiler;
use Millancore\Pesto\Environment;
use Millancore\Pesto\Filter\CoreFiltersStack;
use Millancore\Pesto\Filter\FilterRegister;
use Millancore\Pesto\Loader\FileSystemLoader;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(PestoFactory::class)]
#[UsesClass(Environment::class)]
#[UsesClass(FileSystemCache::class)]
#[UsesClass(DomCompiler::class)]
#[UsesClass(PestoCompiler::class)]
#[UsesClass(CoreFiltersStack::class)]
#[UsesClass(FilterRegister::class)]
#[UsesClass(FileSystemLoader::class)]
class PestoFactoryTest extends TestCase
{
    public function testCreateEnvironmentInstanceFromFactory(): void
    {
        $environment = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );

        $this->assertInstanceOf(Environment::class, $environment);
    }
}
