<?php

declare(strict_types=1);

namespace Millancore\Pesto\Tests\Feature;

use Millancore\Pesto\Environment;
use Millancore\Pesto\Exception\FilterException;
use Millancore\Pesto\PestoFactory;
use Millancore\Pesto\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class FilterTest extends TestCase
{
    private Environment $env;

    public function setUp(): void
    {
        $this->env = PestoFactory::create(
            self::TEMPLATE_PATH,
            self::CACHE_PATH,
        );
    }

    public function test_it_simple_title_filter(): void
    {
        $template = <<<HTML
        <h1>{{ 'pesto is a great engine' | title }}</h1>
        HTML;

        $templateName = $this->createTemporaryTemplate('filter-title', $template);
        $content = $this->env->make($templateName);

        $this->assertEquals('<h1>Pesto Is A Great Engine</h1>', $content->toHtml());
    }

    public function test_it_filter_with_arguments(): void
    {
        $names = ['juan', 'maria', 'jhon'];

        $template = <<<HTML
<h1>{{ \$names | join:',' }}</h1>
HTML;
        $templateName = $this->createTemporaryTemplate('filter-join', $template);
        $content = $this->env->make($templateName, ['names' => $names]);

        $this->assertEquals('<h1>juan, maria, jhon</h1>', $content->toHtml());
    }

    public function test_trow_exception_if_filter_not_found(): void
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Filter "notfound" not found');

        $template = <<<HTML
        <h1>{{ 'name' | notfound }}</h1>
        HTML;

        $templateName = $this->createTemporaryTemplate('filter-notfound', $template);

        $this->env->make($templateName)->toHtml();
    }
}
