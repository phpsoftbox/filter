<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use PhpSoftBox\Inflector\Transliterator;

use function function_exists;
use function mb_strtolower;
use function preg_quote;
use function preg_replace;
use function strtolower;
use function trim;

/**
 * Формирует URL-дружественный slug из строки.
 *
 * По умолчанию строка транслитерируется в ASCII тем же {@see Transliterator}, что и Inflector::urlize()
 * («Привет мир» → «privet-mir»), поэтому с настройками по умолчанию результат совпадает с urlize().
 * С allowUnicode: true буквы любых алфавитов сохраняются, а нижний регистр — unicode-aware («Привет мир» →
 * «привет-мир»).
 *
 * Пример:
 * <code>
 * $filter = new SlugFilter();
 * $filter('Hello, world!'); // 'hello-world'
 * $filter('Склад №1');      // 'sklad-no1'
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

        if (!$this->allowUnicode) {
            $string = Transliterator::toAscii($string);
        }

        $pattern = $this->allowUnicode ? '/[^\p{L}\p{N}]+/u' : '/[^a-zA-Z0-9]+/';
        $slug    = preg_replace($pattern, $this->separator, $string) ?? '';

        if ($this->separator !== '') {
            $quoted = preg_quote($this->separator, '/');
            $slug   = preg_replace('/(?:' . $quoted . ')+/u', $this->separator, $slug) ?? $slug;
            $slug   = trim($slug, $this->separator);
        }

        if ($this->lowercase) {
            $slug = function_exists('mb_strtolower') ? mb_strtolower($slug) : strtolower($slug);
        }

        return $slug;
    }
}
