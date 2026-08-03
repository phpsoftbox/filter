<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function is_float;
use function is_int;
use function preg_match;
use function trim;

/**
 * Приводит значение к int, возвращает default при невалидном вводе.
 *
 * Пример:
 * <code>
 * $filter = new IntegerFilter();
 * $filter('42'); // 42
 * </code>
 */
final readonly class IntegerFilter implements FilterInterface
{
    /**
     * @param int|null $default Значение по умолчанию, если не удалось распознать.
     */
    public function __construct(
        private ?int $default = null,
    ) {
    }

    public function __invoke(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return $this->default;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        FilterValueGuard::guard($value, self::class);
        $string = trim((string) $value);
        if ($string === '') {
            return $this->default;
        }

        if (preg_match('/^-?\d+$/', $string) !== 1) {
            return $this->default;
        }

        return (int) $string;
    }
}
