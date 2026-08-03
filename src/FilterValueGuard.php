<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use InvalidArgumentException;

use function is_array;
use function is_object;
use function method_exists;

final class FilterValueGuard
{
    public static function guard(mixed $value, string $filter): void
    {
        if (is_array($value)) {
            throw new InvalidArgumentException($filter . ' does not accept array values.');
        }

        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new InvalidArgumentException($filter . ' expects a stringable object.');
        }
    }

    public static function toStringValue(mixed $value, string $filter): string
    {
        self::guard($value, $filter);

        return $value === null ? '' : (string) $value;
    }
}
