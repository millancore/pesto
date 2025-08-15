<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;

class PartialTest extends TestCase
{
    private Environment $environment;

    public function setUp(): void
    {
        $this->environment = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function testRenderPartial(): void
    {
        $this->refreshCache();

        ob_start();
        $this->environment->render('composition-list.php');
        $content = ob_get_clean();

        file_put_contents(__DIR__.'/../fixtures/cache/composition-list.php', $content);
        // dd($content);
    }
}
