<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function trim;

/**
 * Нормализует строку: trim + null для пустого значения.
 *
 * Пример:
 * <code>
 * $filter = new StringFilter();
 * $filter('  hello  '); // 'hello'
 * $filter('   '); // null
 * </code>
 */
final readonly class StringFilter implements FilterInterface
{
    public function __invoke(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        FilterValueGuard::guard($value, self::class);
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
