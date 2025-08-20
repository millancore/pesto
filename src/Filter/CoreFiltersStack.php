<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

use Millancore\Pesto\Contract\Htmlable;
use Millancore\Pesto\Contract\StackFilter;

class CoreFiltersStack implements StackFilter
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
            throw new \InvalidArgumentException('Cannot escape array in HTML context. Use {!! !!} for raw output or explicitly convert to string.');
        }

        if (is_object($value)) {
            if ($value instanceof Htmlable) {
                return $value->toHtml();
            }

            throw new \InvalidArgumentException('To print an object, implement __toString() method in it, or implement Htmlable');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public function escapeUrl(string $value): string
    {
        return rawurlencode($value);
    }

    public function escapeJs(mixed $value): string
    {
        $result = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        if (!$result) {
            return 'null';
        }

        return $result;
    }

    public function escapeCss(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_#%\s\.]/', '', $value) ?? '';
    }
}
