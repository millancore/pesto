<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

use Millancore\Pesto\Contract\FilterStack;

class StringFilters implements FilterStack
{
    function getFilters(): array
    {
        return [
            'upper' => fn (string $string) => strtoupper($string),
            'lower' => fn (string $string) => strtolower($string),
            'capitalize' => fn (string $string) => ucfirst(strtolower($string)),
            'title' => fn (string $string) => ucwords(strtolower($string)),
            'trim' => [$this, 'trim'],
            'nl2br' => fn (string $string) => nl2br($string, false),
            'strip_tags' => [$this, 'stripTags'],
            'slug' => [$this, 'slug'],
            'join' => [$this, 'join'],
        ];
    }

    #[AsFilter(name: 'trim')]
    public function trim(string $string, string $characters = " 	\n\r\0\x0B"): string
    {
        return trim($string, $characters);
    }

    #[AsFilter(name: 'strip_tags')]
    public function stripTags(string $string, string|array $allowed_tags = ''): string
    {
        return strip_tags($string, $allowed_tags);
    }

    #[AsFilter(name: 'slug')]
    public function slug(string $string, string $separator = '-'): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^\w\s-]+/', '', $string);
        $string = preg_replace('/\s+/', $separator, $string);
        $string = preg_replace('/-+/', $separator, $string);
        return trim($string, $separator);
    }

    #[AsFilter(name: 'join')]
    #[AsFilter(name: 'implode')]
    public function join(array $pieces, string $glue): string
    {
        return implode($glue, $pieces);
    }
}
