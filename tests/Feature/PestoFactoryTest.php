<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\Filter\AsFilter;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
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

    public function testCreateEnvFromFactoryWithFilters()
    {
        $env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
            [
                new class {
                    #[AsFilter(name: 'extra_filter')]
                    public function filter($value)
                    {
                        return $value.'_extra_filter';
                    }
                },
            ],
        );

        $this->assertSame('test_extra_filter', $env->output('test', ['extra_filter']));
    }
}
