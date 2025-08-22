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

    public function testItSimpleTitleFilter(): void
    {
        $template = <<<HTML
        <h1>{{ 'pesto is a great engine' | title }}</h1>
        HTML;

        $templateName = $this->uniqueTemplateName('filter-title');

        $this->createTemporaryTemplate($templateName, $template);
        $content = $this->env->make($templateName);

        $this->assertEquals('<h1>Pesto Is A Great Engine</h1>', $content->toHtml());
    }

    public function testItFilterWithArguments(): void
    {
        $names = ['juan', 'maria', 'jhon'];

        $template = <<<HTML
<h1>{{ \$names | join:',' }}</h1>
HTML;
        $templateName = $this->uniqueTemplateName('filter-join');

        $this->createTemporaryTemplate($templateName, $template);
        $content = $this->env->make($templateName, ['names' => $names]);

        $this->assertEquals('<h1>juan, maria, jhon</h1>', $content->toHtml());
    }

    public function testTrowExceptionIfFilterNotFound(): void
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage('Filter "notfound" not found');

        $template = <<<HTML
        <h1>{{ 'name' | notfound }}</h1>
        HTML;

        $templateName = $this->uniqueTemplateName('filter-notfound');

        $this->createTemporaryTemplate($templateName, $template);

        $this->env->make($templateName)->toHtml();
    }
}
