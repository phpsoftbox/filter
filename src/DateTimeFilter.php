<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use Throwable;

use function is_array;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function method_exists;

/**
 * Нормализует входные данные в строку даты/времени по заданному формату.
 *
 * Пример:
 * <code>
 * $filter = new DateTimeFilter(format: 'Y-m-d H:i:s', timezone: 'UTC');
 * $filter(1700000000); // '2023-11-14 22:13:20'
 * </code>
 */
final readonly class DateTimeFilter implements FilterInterface
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
        private ?string $timezone = null,
        private bool $returnOriginalOnError = false,
    ) {
    }

    public function __invoke(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            throw new InvalidArgumentException(self::class . ' does not accept array values.');
        }

        if (is_object($value) && !$value instanceof DateTimeInterface && !method_exists($value, '__toString')) {
            throw new InvalidArgumentException(self::class . ' expects a stringable object or DateTimeInterface.');
        }

        try {
            $timezone = $this->timezone !== null ? new DateTimeZone($this->timezone) : null;

            if ($value instanceof DateTimeInterface) {
                $date = DateTimeImmutable::createFromInterface($value);
            } elseif (is_int($value) || (is_string($value) && is_numeric($value))) {
                $date = new DateTimeImmutable('@' . (string) $value);
            } else {
                $date = new DateTimeImmutable((string) $value, $timezone);
            }

            if ($timezone !== null) {
                $date = $date->setTimezone($timezone);
            }

            return $date->format($this->format);
        } catch (Throwable) {
            if ($this->returnOriginalOnError) {
                return (string) $value;
            }

            return null;
        }
    }
}
