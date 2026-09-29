<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use PhpSoftBox\Filter\IntegerFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const INF;
use const NAN;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

#[CoversClass(IntegerFilter::class)]
#[CoversMethod(IntegerFilter::class, '__invoke')]
final class IntegerFilterTest extends TestCase
{
    /**
     * Значения, которые распознаются как int.
     *
     * @return array<string, array{mixed, int}>
     */
    public static function validValues(): array
    {
        return [
            'int'                => [42, 42],
            'numeric string'     => ['42', 42],
            'string with spaces' => [' -42 ', -42],
            'plus sign'          => ['+7', 7],
            'leading zeros'      => ['007', 7],
            'negative zero'      => ['-0', 0],
            'integral float'     => [2.0, 2],
            'max int string'     => ['9223372036854775807', PHP_INT_MAX],
            'min int string'     => ['-9223372036854775808', PHP_INT_MIN],
        ];
    }

    /**
     * Значения, для которых возвращается default: дробные и вне диапазона int.
     *
     * @return array<string, array{mixed}>
     */
    public static function invalidValues(): array
    {
        return [
            'float with fraction'     => [1.9],
            'negative float fraction' => [-1.5],
            'string with fraction'    => ['1.5'],
            'overflow string'         => ['99999999999999999999'],
            'max int + 1 string'      => ['9223372036854775808'],
            'huge float'              => [1e30],
            'huge negative float'     => [-1e30],
            'float at 2^63'           => [9.2233720368547758E18],
            'nan'                     => [NAN],
            'inf'                     => [INF],
            'exponent string'         => ['1e3'],
            'text'                    => ['abc'],
            'empty string'            => [''],
            'null'                    => [null],
        ];
    }

    /**
     * Проверим, что корректное целое значение приводится к int без потерь.
     *
     * @see IntegerFilter::__invoke()
     */
    #[Test]
    #[DataProvider('validValues')]
    public function convertsValidValue(mixed $value, int $expected): void
    {
        $filter = new IntegerFilter();

        self::assertSame($expected, $filter($value));
    }

    /**
     * Проверим, что дробное значение и значение вне диапазона int дают default, а не усечённое число.
     *
     * @see IntegerFilter::__invoke()
     */
    #[Test]
    #[DataProvider('invalidValues')]
    public function returnsDefaultForInvalidValue(mixed $value): void
    {
        $filter = new IntegerFilter(default: -1);

        self::assertSame(-1, $filter($value));
    }
}
