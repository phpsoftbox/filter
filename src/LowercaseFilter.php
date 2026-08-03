<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function function_exists;
use function mb_strtolower;
use function strtolower;

/**
 * Приводит строку к нижнему регистру.
 *
 * Пример:
 * <code>
 * $filter = new LowercaseFilter();
 * $filter('HeLLo'); // 'hello'
 * </code>
 */
final readonly class LowercaseFilter implements FilterInterface
{
    public function __construct(
        private ?string $encoding = 'UTF-8',
    ) {
    }

    public function __invoke(mixed $value): string
    {
        $string = FilterValueGuard::toStringValue($value, self::class);

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($string, $this->encoding ?? 'UTF-8');
        }

        return strtolower($string);
    }
}
