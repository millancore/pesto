<?php

namespace Millancore\Pesto\Tests;


use Millancore\Pesto\Contract\CompilerPass;
use Millancore\Pesto\Pesto;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function assertCompiledEquals(CompilerPass $pass, string $expected, string $html) : void
    {
        $pesto = new Pesto($html);

        $pass->compile($pesto);
        $this->assertEquals($expected, $pesto->getCompiledTemplate());
    }

}