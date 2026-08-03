<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function in_array;
use function is_bool;
use function is_float;
use function is_int;
use function strtolower;
use function trim;

/**
 * Приводит значение к boolean, возвращает default при невалидном вводе.
 *
 * Пример:
 * <code>
 * $filter = new BooleanFilter();
 * $filter('yes'); // true
 * </code>
 */
final readonly class BooleanFilter implements FilterInterface
{
    /**
     * @param bool|null $default Значение по умолчанию, если не удалось распознать.
     */
    public function __construct(
        private ?bool $default = null,
    ) {
    }

    public function __invoke(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return $this->default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        FilterValueGuard::guard($value, self::class);
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return $this->default;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return $this->default;
    }
}
