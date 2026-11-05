<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PhpSoftBox\Filter\PhoneFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhoneFilter::class)]
#[CoversClass(PhoneDriverEnum::class)]
#[CoversMethod(PhoneFilter::class, '__invoke')]
#[CoversMethod(PhoneDriverEnum::class, 'create')]
final class PhoneFilterTest extends TestCase
{
    /**
     * Проверим, что RU по умолчанию принимает только мобильные номера: городской — null.
     *
     * @see PhoneFilter::__invoke()
     */
    #[Test]
    public function ruRejectsLandlineByDefault(): void
    {
        $filter = new PhoneFilter();

        self::assertNull($filter('+7 (495) 123-45-67'));
    }

    /**
     * Проверим, что с mobileOnly: false RU принимает городской номер.
     *
     * @see PhoneFilter::__invoke()
     * @see PhoneDriverEnum::create()
     */
    #[Test]
    public function ruAcceptsLandlineWhenMobileOnlyDisabled(): void
    {
        $filter = new PhoneFilter(mobileOnly: false);

        self::assertSame('4951234567', $filter('8 (495) 123-45-67'));
    }

    /**
     * Проверим, что у 10-значного номера на 8 (800) не отрезается первая цифра как код страны.
     *
     * @see PhoneFilter::__invoke()
     */
    #[Test]
    public function ruKeepsTollFreeNumberWithoutCountryCode(): void
    {
        $filter = new PhoneFilter(mobileOnly: false);

        self::assertSame('8001234567', $filter('800 123-45-67'));
    }

    /**
     * Проверим, что KZ-драйвер нормализует номер.
     *
     * @see PhoneFilter::__invoke()
     */
    #[Test]
    public function kzNormalizesPhone(): void
    {
        $filter = new PhoneFilter(PhoneDriverEnum::KZ);

        self::assertSame('7011234567', $filter('+7 (701) 123-45-67'));
    }

    /**
     * Проверим, что с prepareForDb и withCountryCode номер сохраняется в E.164.
     *
     * @see PhoneFilter::__invoke()
     */
    #[Test]
    public function prepareForDbWithCountryCodeReturnsE164(): void
    {
        $filter = new PhoneFilter(withCountryCode: true);

        self::assertSame('+79991234567', $filter('8 (999) 123-45-67'));
    }

    /**
     * Проверим, что драйвер Китая нормализует мобильный номер с кодом +86 и 0086.
     *
     * @see PhoneFilter::__invoke()
     * @see PhoneDriverEnum::create()
     */
    #[Test]
    public function cnNormalizesMobileNumber(): void
    {
        $filter = new PhoneFilter(PhoneDriverEnum::CN);

        self::assertSame(['13812345678', '13812345678'], [$filter('+86 138 1234 5678'), $filter('0086 13812345678')]);
    }

    /**
     * Проверим, что драйвер Китая форматирует номер группами 3-4-4.
     *
     * @see PhoneFilter::__invoke()
     */
    #[Test]
    public function cnFormatsNumberInGroupsOfThreeFourFour(): void
    {
        $filter = new PhoneFilter(PhoneDriverEnum::CN, prepareForDb: false, withCountryCode: true);

        self::assertSame('+86 138 1234 5678', $filter('13812345678'));
    }

    /**
     * Проверим, что номер Китая не с 1 и неверной длины отклоняется.
     *
     * @see PhoneFilter::__invoke()
     */
    #[Test]
    public function cnRejectsNonMobileNumbers(): void
    {
        $filter = new PhoneFilter(PhoneDriverEnum::CN);

        self::assertSame([null, null], [$filter('+86 21 1234 5678'), $filter('+86 138 1234 567')]);
    }
}
