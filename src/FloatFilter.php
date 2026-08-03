<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function is_float;
use function is_int;
use function is_numeric;
use function trim;

/**
 * Приводит значение к float, возвращает default при невалидном вводе.
 *
 * Пример:
 * <code>
 * $filter = new FloatFilter();
 * $filter('3.14'); // 3.14
 * </code>
 */
final readonly class FloatFilter implements FilterInterface
{
    /**
     * @param float|null $default Значение по умолчанию, если не удалось распознать.
     */
    public function __construct(
        private ?float $default = null,
    ) {
    }

    public function __invoke(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return $this->default;
        }

        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        FilterValueGuard::guard($value, self::class);
        $string = trim((string) $value);
        if ($string === '') {
            return $this->default;
        }

        if (!is_numeric($string)) {
            return $this->default;
        }

        return (float) $string;
    }
}
