<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function str_ireplace;
use function str_replace;

/**
 * Заменяет подстроки в строке (опционально без учета регистра).
 *
 * Пример:
 * <code>
 * $filter = new StrReplaceFilter(' ', '-');
 * $filter('hello world'); // 'hello-world'
 * </code>
 */
final readonly class StrReplaceFilter implements FilterInterface
{
    /**
     * @param string|array<string> $search
     * @param string|array<string> $replace
     */
    public function __construct(
        private string|array $search,
        private string|array $replace,
        private bool $caseInsensitive = false,
    ) {
    }

    public function __invoke(mixed $value): string
    {
        $string = FilterValueGuard::toStringValue($value, self::class);

        if ($this->caseInsensitive) {
            return (string) str_ireplace($this->search, $this->replace, $string);
        }

        return (string) str_replace($this->search, $this->replace, $string);
    }
}
