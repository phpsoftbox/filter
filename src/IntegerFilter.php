<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function filter_var;
use function floor;
use function is_finite;
use function is_float;
use function is_int;
use function ltrim;
use function preg_match;
use function trim;

use const FILTER_VALIDATE_INT;

/**
 * Приводит значение к int, возвращает default при невалидном вводе.
 *
 * Принимаются: int; float без дробной части в диапазоне int (2.0 → 2); строка из цифр со знаком
 * (" -42 ", "+7", "007") в диапазоне int. Всё остальное — default, значение не усекается и не округляется:
 * дробные числа (1.9, "1.5"), значения вне диапазона int ("99999999999999999999", 1e30), NAN/INF,
 * строки с экспонентой и пробелами внутри.
 *
 * Пример:
 * <code>
 * $filter = new IntegerFilter();
 * $filter('42');  // 42
 * $filter(1.9);   // null
 * </code>
 */
final readonly class IntegerFilter implements FilterInterface
{
    /**
     * Граница диапазона int в float: (float) PHP_INT_MAX округляется до 2^63, которое уже не помещается в int.
     */
    private const float INT_UPPER_BOUND = 9223372036854775808.0;

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
            return $this->fromFloat($value);
        }

        FilterValueGuard::guard($value, self::class);
        $string = trim((string) $value);

        if (preg_match('/^([+-]?)(\d+)$/', $string, $matches) !== 1) {
            return $this->default;
        }

        // FILTER_VALIDATE_INT отклоняет переполнение, но не принимает ведущие нули — они снимаются.
        $digits = ltrim($matches[2], '0');
        $int    = filter_var($matches[1] . ($digits === '' ? '0' : $digits), FILTER_VALIDATE_INT);

        return $int === false ? $this->default : $int;
    }

    private function fromFloat(float $value): ?int
    {
        if (!is_finite($value) || floor($value) !== $value) {
            return $this->default;
        }

        if ($value >= self::INT_UPPER_BOUND || $value < -self::INT_UPPER_BOUND) {
            return $this->default;
        }

        return (int) $value;
    }
}
