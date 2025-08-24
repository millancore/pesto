<?php

declare(strict_types=1);

namespace Millancore\Pesto\Filter;

use Millancore\Pesto\Contract\FilterStack;
use Millancore\Pesto\Contract\Htmlable;

class CoreFilters implements FilterStack
{
    public function getFilters(): array
    {
        return [
            'raw' => fn ($value) => $value,
            'escape' => [$this, 'escape'],
        ];
    }

    public function escape(mixed $value, string $context = 'html'): string
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        if (is_array($value)) {
            throw new \InvalidArgumentException('Cannot escape array in HTML context. use {{ $arr | join }} filter to convert to string');
        }

        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new \InvalidArgumentException('To print an object, implement __toString() method in it, or implement Htmlable');
        }

        $value = (string) $value;

        return match ($context) {
            'js' => $this->escapeJs($value),
            'on_attribute' => $this->escapeJS($value),
            'css' => $this->escapeCss($value),
            'url' => $this->escapeUrl($value),
            'comment' => $this->escapeComment($value),
            default => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        };
    }

    public function escapeUrl(string $value): string
    {
        $url = trim($value);
        if (str_starts_with(strtolower($url), 'javascript:')) {
            return '';
        }

        return rawurlencode($url);
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

    private function escapeComment(string $value): string
    {
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return str_replace('--', '- -', $value);
    }
}
