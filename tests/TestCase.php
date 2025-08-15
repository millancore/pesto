<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests;

use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Pesto;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public const string TEMPLATE_PATH = __DIR__.'/fixtures/templates';
    public const string CACHE_PATH = __DIR__.'/fixtures/cache';

    public function assertCompiledEquals(CompilerPass $pass, string $expected, string $html): void
    {
        $pesto = new Pesto($html);

        $pass->compile($pesto);
        $this->assertEquals($expected, $pesto->getCompiledTemplate());
    }

    protected function refreshCache(): void
    {
        array_map('unlink', glob(self::CACHE_PATH.'/*'));
    }

    protected function createTemporaryTemplate(string $name, string $content): void
    {
        file_put_contents(self::TEMPLATE_PATH . '/' . $name, $content);
    }

    protected function cleanupTemporaryTemplate(): void
    {
        array_map('unlink', glob(self::TEMPLATE_PATH.'/*'));
    }
}
