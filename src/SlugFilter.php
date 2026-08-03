<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function function_exists;
use function iconv;
use function is_string;
use function preg_quote;
use function preg_replace;
use function strtolower;
use function trim;

/**
 * Формирует URL-дружественный slug из строки.
 *
 * Пример:
 * <code>
 * $filter = new SlugFilter();
 * $filter('Hello, world!'); // 'hello-world'
 * </code>
 */
final readonly class SlugFilter implements FilterInterface
{
    public function __construct(
        private string $separator = '-',
        private bool $lowercase = true,
        private bool $allowUnicode = false,
    ) {
    }

    public function __invoke(mixed $value): string
    {
        $string = FilterValueGuard::toStringValue($value, self::class);
        $string = trim($string);

        if ($string === '') {
            return '';
        }

        if (!$this->allowUnicode && function_exists('iconv')) {
            $translit = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
            if (is_string($translit) && $translit !== '') {
                $string = $translit;
            }
        }

        $pattern = $this->allowUnicode ? '/[^\p{L}\p{N}]+/u' : '/[^a-zA-Z0-9]+/';
        $slug    = preg_replace($pattern, $this->separator, $string) ?? '';

        $quoted = preg_quote($this->separator, '/');
        $slug   = preg_replace('/' . $quoted . '+/', $this->separator, $slug) ?? $slug;
        $slug   = trim($slug, $this->separator);

        if ($this->lowercase) {
            $slug = strtolower($slug);
        }

        return $slug;
    }
}
