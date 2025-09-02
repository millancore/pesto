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
        \$myObject->property = 'Hello Pesto';
        echo \$myObject->property;
    ?>
</div>
HTML;

        $templateName = $this->createTemporaryTemplate('php-block-test', $template);
        $content = $this->env->make($templateName);

        $this->assertEquals('<div>Hello Pesto</div>', $content->toHtml());
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

    public function test_render_php_block_in_html_attribute(): void
    {
        $template = <<<HTML
<div title="<?= \$myObject->property ?>"></div>
HTML;
        $myObject = new \stdClass();
        $myObject->property = 'Hello Again';
        $templateName = $this->createTemporaryTemplate('php-echo-block-test', $template);
        $content = $this->env->make($templateName, ['myObject' => $myObject]);

        $this->assertEquals('<div title="Hello Again"></div>', $content->toHtml());
    }

    public function test_render_php_at_first_line(): void
    {
        $template = <<<HTML
<?php \$myObject = new \stdClass(); \$myObject->property = 'First line'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
             <meta name="viewport" content="width=device-width, initial-scale=1">
             <title>Document</title>
</head>
<body>
    <h1>{{ \$myObject->property }}</h1>
</body>
</html>
HTML;

        $templateName = $this->createTemporaryTemplate('php-first-line', $template);
        $content = $this->env->make($templateName);

        $this->assertEquals(<<<HTML
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"></meta>
             <meta name="viewport" content="width=device-width, initial-scale=1"></meta>
             <title>Document</title>
</head>
<body>
    <h1>First line</h1>

</body></html>
HTML, $content->toHtml());
    }

    public function test_render_multiples_php_blocks_before_html_content(): void
    {
        $template = <<<HTML
<?php \$myObject = new \stdClass();  ?>
<?php \$myObject->property = 'Second line'; ?>
<!doctype html>
<html lang="en">
<body>
 <h1>{{ \$myObject->property }}</h1>  
</body>
</html>
HTML;
        $templateName = $this->createTemporaryTemplate('php-multiple-blocks', $template);
        $content = $this->env->make($templateName);

        $this->assertEquals(<<<HTML
<!DOCTYPE html>
<html lang="en"><head></head><body>
 <h1>Second line</h1>  

</body></html>
HTML, $content->toHtml());
    }
}
