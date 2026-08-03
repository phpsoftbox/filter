<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function is_string;
use function trim;

/**
 * Возвращает null для пустых строк (опционально trim).
 *
 * Пример:
 * <code>
 * $filter = new NullIfEmptyFilter();
 * $filter('   '); // null
 * </code>
 */
final readonly class NullIfEmptyFilter implements FilterInterface
{
    public function __construct(
        private bool $trim = true,
    ) {
    }

    public function __invoke(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        FilterValueGuard::guard($value, self::class);

        if (!is_string($value)) {
            return $value;
        }

        $string = $this->trim ? trim($value) : $value;

        return $string === '' ? null : $string;
    }
}
