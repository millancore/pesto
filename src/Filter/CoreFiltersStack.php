<?php

namespace Millancore\Pesto\Filter;

use InvalidArgumentException;
use Millancore\Pesto\Contract\HtmlableInterface;

class CoreFiltersStack implements StackFilterInterface
{

    public function getFilters(): array
    {
        return [
            'escape' => [$this, 'escape'],
            'attr' => [$this, 'escape'],
            'url' => [$this, 'escapeUrl'],
            'js' => [$this, 'escapeJs'],
            'css' => [$this, 'escapeCss'],
        ];
    }

    public function escape(mixed $value): string
    {
        if (is_array($value)) {
            throw new InvalidArgumentException('Cannot escape array in HTML context. Use {!! !!} for raw output or explicitly convert to string.');
        }

        if (is_object($value)) {

            if ($value instanceof HtmlableInterface) {
                return $value->toHtml();
            }

            throw new InvalidArgumentException('To print an object, implement __toString() method in it, or implement HtmlableInterface');
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function escapeUrl(string $value): string
    {
       return rawurlencode($value);
    }

    public function escapeJs(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public function escapeCss(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_#%\s\.]/', '', $value);
    }
}