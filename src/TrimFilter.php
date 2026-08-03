<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function trim;

/**
 * Обрезает пробелы (или заданные символы) по краям строки.
 *
 * Пример:
 * <code>
 * $filter = new TrimFilter();
 * $filter('  hello  '); // 'hello'
 * </code>
 */
final readonly class TrimFilter implements FilterInterface
{
    public function __construct(
        private ?string $chars = null,
    ) {
    }

    public function __invoke(mixed $value): mixed
    {
        $string = FilterValueGuard::toStringValue($value, self::class);

        return $this->chars === null ? trim($string) : trim($string, $this->chars);
    }
}
