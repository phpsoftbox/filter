<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function preg_replace;

/**
 * Удаляет все нецифровые символы.
 *
 * Пример:
 * <code>
 * $filter = new DigitsFilter();
 * $filter('+7 (999) 123-45-67'); // '79991234567'
 * </code>
 */
final class DigitsFilter implements FilterInterface
{
    public function __invoke(mixed $value): string
    {
        $string = FilterValueGuard::toStringValue($value, self::class);
        $digits = preg_replace('/\D+/', '', $string);

        return $digits ?? '';
    }
}
