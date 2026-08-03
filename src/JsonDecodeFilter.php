<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use InvalidArgumentException;

use function is_array;
use function is_object;
use function is_string;
use function json_decode;
use function json_last_error;
use function method_exists;

use const JSON_ERROR_NONE;

/**
 * Декодирует JSON-строку в массив/объект.
 *
 * Пример:
 * <code>
 * $filter = new JsonDecodeFilter();
 * $filter('{"a":1}'); // ['a' => 1]
 * </code>
 */
final readonly class JsonDecodeFilter implements FilterInterface
{
    public function __construct(
        private bool $assoc = true,
        private int $depth = 512,
        private int $flags = 0,
        private mixed $default = null,
        private bool $returnOriginalOnError = false,
    ) {
    }

    public function __invoke(mixed $value): mixed
    {
        if (is_array($value)) {
            throw new InvalidArgumentException(self::class . ' does not accept array values.');
        }

        if (is_object($value)) {
            if (!method_exists($value, '__toString')) {
                throw new InvalidArgumentException(self::class . ' expects a stringable object.');
            }

            $value = (string) $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, $this->assoc, $this->depth, $this->flags);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->returnOriginalOnError ? $value : $this->default;
        }

        return $decoded;
    }
}
