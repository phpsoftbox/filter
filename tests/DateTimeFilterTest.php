<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use PhpSoftBox\Filter\DateTimeFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeFilter::class)]
#[CoversMethod(DateTimeFilter::class, '__invoke')]
final class DateTimeFilterTest extends TestCase
{
    /**
     * Проверим, что числовая строка по умолчанию разбирается как дата (20240115 — 15 января 2024), а не timestamp.
     *
     * @see DateTimeFilter::__invoke()
     */
    #[Test]
    public function parsesNumericStringAsDateByDefault(): void
    {
        $filter = new DateTimeFilter(format: 'Y-m-d', timezone: 'UTC');

        self::assertSame('2024-01-15', $filter('20240115'));
    }

    /**
     * Проверим, что int по-прежнему считается Unix timestamp.
     *
     * @see DateTimeFilter::__invoke()
     */
    #[Test]
    public function treatsIntAsTimestamp(): void
    {
        $filter = new DateTimeFilter(timezone: 'UTC');

        self::assertSame('2023-11-14 22:13:20', $filter(1700000000));
    }

    /**
     * Проверим, что с numericStringAsTimestamp числовая строка считается timestamp.
     *
     * @see DateTimeFilter::__invoke()
     */
    #[Test]
    public function treatsNumericStringAsTimestampWhenEnabled(): void
    {
        $filter = new DateTimeFilter(timezone: 'UTC', numericStringAsTimestamp: true);

        self::assertSame('2023-11-14 22:13:20', $filter('1700000000'));
    }

    /**
     * Проверим, что числовая строка, которая не является датой, без флага даёт null.
     *
     * @see DateTimeFilter::__invoke()
     */
    #[Test]
    public function returnsNullForNumericStringThatIsNotDate(): void
    {
        $filter = new DateTimeFilter(timezone: 'UTC');

        self::assertNull($filter('1700000000'));
    }
}
