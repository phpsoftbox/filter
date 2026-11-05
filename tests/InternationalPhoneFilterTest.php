<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use PhpSoftBox\Filter\InternationalPhoneFilter;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InternationalPhoneFilter::class)]
#[CoversMethod(InternationalPhoneFilter::class, '__invoke')]
final class InternationalPhoneFilterTest extends TestCase
{
    /**
     * Проверим, что фильтр сохраняет номер в E.164 с учётом выбранной страны.
     *
     * @see InternationalPhoneFilter::__invoke()
     */
    #[Test]
    public function normalizesToE164ByCountry(): void
    {
        $filter = new InternationalPhoneFilter(PhoneDriverEnum::CN);

        self::assertSame('+8613812345678', $filter('138-1234-5678'));
    }

    /**
     * Проверим, что неверный номер даёт null, а с keepOriginalOnError — исходную строку.
     *
     * @see InternationalPhoneFilter::__invoke()
     */
    #[Test]
    public function returnsNullOrOriginalOnError(): void
    {
        self::assertSame(
            [null, '12345'],
            [(new InternationalPhoneFilter())('12345'), (new InternationalPhoneFilter(keepOriginalOnError: true))('12345')],
        );
    }
}
