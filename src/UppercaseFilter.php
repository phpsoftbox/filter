<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function function_exists;
use function mb_strtoupper;
use function strtoupper;

/**
 * Приводит строку к верхнему регистру.
 *
 * Пример:
 * <code>
 * $filter = new UppercaseFilter();
 * $filter('Hello'); // 'HELLO'
 * </code>
 */
final readonly class UppercaseFilter implements FilterInterface
{
    public function __construct(
        private ?string $encoding = 'UTF-8',
    ) {
    }

    public function __invoke(mixed $value): string
    {
        $string = FilterValueGuard::toStringValue($value, self::class);

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($string, $this->encoding ?? 'UTF-8');
        }

        return strtoupper($string);
    }
}
