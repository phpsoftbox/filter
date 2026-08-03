<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PhpSoftBox\Filter\ArrayFilter;
use PhpSoftBox\Filter\BooleanFilter;
use PhpSoftBox\Filter\DateTimeFilter;
use PhpSoftBox\Filter\DefaultFilter;
use PhpSoftBox\Filter\DigitsFilter;
use PhpSoftBox\Filter\EmptyFilter;
use PhpSoftBox\Filter\ExplodeFilter;
use PhpSoftBox\Filter\FloatFilter;
use PhpSoftBox\Filter\IntegerFilter;
use PhpSoftBox\Filter\JsonDecodeFilter;
use PhpSoftBox\Filter\ListFilter;
use PhpSoftBox\Filter\LowercaseFilter;
use PhpSoftBox\Filter\NullIfEmptyFilter;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PhpSoftBox\Filter\PhoneFilter;
use PhpSoftBox\Filter\PregReplaceFilter;
use PhpSoftBox\Filter\SlugFilter;
use PhpSoftBox\Filter\StringFilter;
use PhpSoftBox\Filter\StrReplaceFilter;
use PhpSoftBox\Filter\TrimFilter;
use PhpSoftBox\Filter\UppercaseFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LowercaseFilter::class)]
#[CoversClass(UppercaseFilter::class)]
#[CoversClass(NullIfEmptyFilter::class)]
#[CoversClass(DigitsFilter::class)]
#[CoversClass(EmptyFilter::class)]
#[CoversClass(ExplodeFilter::class)]
#[CoversClass(SlugFilter::class)]
#[CoversClass(ArrayFilter::class)]
#[CoversClass(ListFilter::class)]
#[CoversClass(JsonDecodeFilter::class)]
#[CoversClass(DateTimeFilter::class)]
#[CoversClass(TrimFilter::class)]
#[CoversClass(StrReplaceFilter::class)]
#[CoversClass(PregReplaceFilter::class)]
#[CoversClass(BooleanFilter::class)]
#[CoversClass(IntegerFilter::class)]
#[CoversClass(FloatFilter::class)]
#[CoversClass(PhoneFilter::class)]
#[CoversClass(DefaultFilter::class)]
#[CoversClass(StringFilter::class)]
final class FiltersTest extends TestCase
{
    /**
     * Проверяет базовую работу TrimFilter и StrReplaceFilter.
     */
    #[Test]
    public function stringFiltersNormalizeValues(): void
    {
        $trim    = new TrimFilter();
        $replace = new StrReplaceFilter(' ', '-', caseInsensitive: false);
        $lower   = new LowercaseFilter();
        $upper   = new UppercaseFilter();

        self::assertSame('hello-world', $lower($replace($trim('  HeLLo WoRLD  '))));
        self::assertSame('HELLO WORLD', $upper('Hello world'));
    }

    /**
     * Проверяет, что строковые фильтры не принимают массивы.
     */
    #[Test]
    public function stringFiltersRejectArrayValues(): void
    {
        $filter = new TrimFilter();

        $this->expectException(InvalidArgumentException::class);
        $filter(['value']);
    }

    /**
     * Проверяет работу PregReplaceFilter.
     */
    #[Test]
    public function pregReplaceFilterWorks(): void
    {
        $filter = new PregReplaceFilter('/\D+/', '');

        self::assertSame('79991234567', $filter('+7 (999) 123-45-67'));
    }

    /**
     * Проверяет DigitsFilter и NullIfEmptyFilter.
     */
    #[Test]
    public function digitsAndNullFiltersWork(): void
    {
        $digits      = new DigitsFilter();
        $nullIfEmpty = new NullIfEmptyFilter();

        self::assertSame('123', $digits('1a2b3'));
        self::assertNull($nullIfEmpty('   '));
        self::assertSame('0', $nullIfEmpty('0'));
    }

    /**
     * Проверяет SlugFilter.
     */
    #[Test]
    public function slugFilterBuildsSlug(): void
    {
        $filter = new SlugFilter();

        self::assertSame('hello-world', $filter('Hello, world!'));
        self::assertSame('multi-space', $filter(' Multi   space '));
    }

    /**
     * Проверяет ArrayFilter и ListFilter.
     */
    #[Test]
    public function arrayFiltersNormalizeLists(): void
    {
        $arrayFilter   = new ArrayFilter();
        $arrayAsList   = new ArrayFilter()->asList();
        $listFilter    = new ListFilter();
        $explodeFilter = new ExplodeFilter(',');

        self::assertSame(['a', ' b', ' ', ' c'], $explodeFilter('a, b, , c'));
        self::assertSame(['first', 'second'], $listFilter([2 => 'first', 5 => 'second']));
        self::assertSame(['a' => 'first', 5 => 'second'], $arrayFilter(['a' => 'first', 5 => 'second']));
        self::assertSame([0 => 'first', 1 => 'second'], $arrayAsList(['a' => 'first', 5 => 'second']));
    }

    /**
     * Проверяет поэлементную фильтрацию в ArrayFilter и ListFilter.
     */
    #[Test]
    public function arrayFiltersApplyPerItemFilters(): void
    {
        $arrayFilter   = new ArrayFilter()->filters(new TrimFilter(), new IntegerFilter());
        $listFilter    = new ListFilter()->filters(new TrimFilter(), new IntegerFilter());
        $explodeFilter = new ExplodeFilter(',');

        self::assertSame([1, null, 3], $arrayFilter($explodeFilter(' 1, x, 3 ')));
        self::assertSame([7, null], $listFilter([' 7 ', 'oops']));
    }

    /**
     * Проверяет skipEmpty в ArrayFilter и ListFilter.
     */
    #[Test]
    public function arrayAndListFiltersSkipEmptyValues(): void
    {
        $arrayFilter = new ArrayFilter(skipEmpty: true);
        $listFilter  = new ListFilter(skipEmpty: true);

        self::assertSame([0 => 'a', 3 => 'b'], $arrayFilter(['a', '', null, 'b']));
        self::assertSame(['a', 'b'], $listFilter(['a', '', null, 'b']));
    }

    /**
     * Проверяет удаление null и пустых строк в EmptyFilter.
     */
    #[Test]
    public function emptyFilterRemovesNullAndEmptyValues(): void
    {
        $nullOnly = new EmptyFilter();
        $allEmpty = new EmptyFilter(removeNull: true, removeEmpty: true);

        self::assertSame([1, '', '  '], $nullOnly([1, null, '', '  ']));
        self::assertSame([1, '0'], $allEmpty([1, null, '', '  ', '0']));
    }

    /**
     * Проверяет JsonDecodeFilter.
     */
    #[Test]
    public function jsonDecodeFilterWorks(): void
    {
        $filter = new JsonDecodeFilter();

        self::assertSame(['a' => 1], $filter('{"a":1}'));
        self::assertNull($filter('{broken'));
    }

    /**
     * Проверяет DateTimeFilter.
     */
    #[Test]
    public function dateTimeFilterNormalizesValue(): void
    {
        $filter   = new DateTimeFilter(format: 'Y-m-d H:i:s', timezone: 'UTC');
        $expected = new DateTimeImmutable('@1700000000')->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        self::assertSame($expected, $filter(1700000000));
    }

    /**
     * Проверяет преобразование к boolean/integer/float.
     */
    #[Test]
    public function typeFiltersCastValues(): void
    {
        $bool   = new BooleanFilter();
        $int    = new IntegerFilter();
        $float  = new FloatFilter();
        $string = new StringFilter();

        self::assertTrue($bool('yes'));
        self::assertFalse($bool('0'));
        self::assertSame(42, $int('42'));
        self::assertSame(3.14, $float('3.14'));
        self::assertSame('hello', $string('  hello  '));
        self::assertNull($string('   '));
    }

    /**
     * Проверяет работу DefaultFilter для null/пустой строки.
     */
    #[Test]
    public function defaultFilterAppliesFallbackForNullAndEmptyString(): void
    {
        $filter = new DefaultFilter('all');

        self::assertSame('all', $filter(null));
        self::assertSame('all', $filter(''));
        self::assertSame('0', $filter('0'));
        self::assertSame(false, $filter(false));
    }

    /**
     * Проверяет, что фильтры типов отклоняют нестроковые объекты.
     */
    #[Test]
    public function typeFiltersRejectNonStringableObject(): void
    {
        $filter = new IntegerFilter();
        $object = new class () {
        };

        $this->expectException(InvalidArgumentException::class);
        $filter($object);
    }

    /**
     * Проверяет, что JsonDecodeFilter отклоняет массивы.
     */
    #[Test]
    public function jsonDecodeFilterRejectsArray(): void
    {
        $filter = new JsonDecodeFilter();

        $this->expectException(InvalidArgumentException::class);
        $filter(['a' => 1]);
    }

    /**
     * Проверяет, что PhoneFilter нормализует номер по умолчанию.
     */
    #[Test]
    public function phoneFilterNormalizesPhone(): void
    {
        $filter = new PhoneFilter();

        self::assertSame('9991234567', $filter('+7 (999) 123-45-67'));
        self::assertNull($filter('123'));
    }

    /**
     * Проверяет переключение режима PhoneFilter для форматированного вывода.
     */
    #[Test]
    public function phoneFilterSwitchesPrepareForDbMode(): void
    {
        $filter = new PhoneFilter();

        $formatted = $filter->switchPrepareForDbTo(false);

        self::assertSame('9991234567', $filter('+7 (999) 123-45-67'));
        self::assertSame('(999) 123-4567', $formatted('+7 (999) 123-45-67'));
    }

    /**
     * Проверяет, что PhoneFilter поддерживает новые country-драйверы.
     */
    #[Test]
    public function phoneFilterSupportsAmAzByDrivers(): void
    {
        $armenia    = new PhoneFilter(PhoneDriverEnum::AM);
        $azerbaijan = new PhoneFilter(PhoneDriverEnum::AZ);
        $belarus    = new PhoneFilter(PhoneDriverEnum::BY);

        self::assertSame('77123456', $armenia('+374 (77) 123-456'));
        self::assertSame('501234567', $azerbaijan('+994 (50) 123-45-67'));
        self::assertSame('291234567', $belarus('+375 (29) 123-45-67'));
    }
}
