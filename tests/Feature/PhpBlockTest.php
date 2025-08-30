<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class PhpBlockTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        $this->env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function tearDown(): void
    {
        $this->refreshCache();
        $this->cleanupTemporaryTemplate();
    }

    public function test_correctly_renders_php_block(): void
    {
        $template = <<<HTML
<div><?php
        \$myObject = new \stdClass();
        \$myObject->property = 'Hello World';
        echo \$myObject->property;
    ?>
</div>
HTML;

        $templateName = $this->createTemporaryTemplate('php-block-test', $template);
        $content = $this->env->make($templateName);

        $this->assertEquals('<div>Hello World</div>', $content->toHtml());
    }

    public function test_correctly_renders_php_echo_blocks(): void
    {
        $template = <<<HTML
<div><?= \$myObject->property ?></div>
HTML;

        $myObject = new \stdClass();
        $myObject->property = 'Hello Again';

        $templateName = $this->createTemporaryTemplate('php-echo-block-test', $template);
        $content = $this->env->make($templateName, ['myObject' => $myObject]);

        $this->assertEquals('<div>Hello Again</div>', $content->toHtml());
    }
}
