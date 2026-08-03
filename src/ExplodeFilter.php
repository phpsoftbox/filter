<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function explode;
use function is_array;
use function is_string;

/**
 * Преобразует строку в массив через explode().
 *
 * Пример:
 * <code>
 * $filter = new ExplodeFilter(',');
 * $filter('a,b,c'); // ['a', 'b', 'c']
 * </code>
 */
final readonly class ExplodeFilter implements FilterInterface
{
    public function __construct(
        private string $delimiter = ',',
    ) {
    }

    public function __invoke(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return explode($this->delimiter, $value);
        }

        return [$value];
    }
}
