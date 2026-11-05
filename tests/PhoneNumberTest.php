<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PhpSoftBox\Filter\Phone\PhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhoneNumber::class)]
#[CoversMethod(PhoneNumber::class, 'parse')]
#[CoversMethod(PhoneNumber::class, 'e164')]
#[CoversMethod(PhoneNumber::class, 'format')]
#[CoversMethod(PhoneDriverEnum::class, 'dialCode')]
#[CoversMethod(PhoneDriverEnum::class, 'detectionOrder')]
final class PhoneNumberTest extends TestCase
{
    /**
     * @return iterable<string, array{string, PhoneDriverEnum, string, string}>
     */
    public static function internationalNumbers(): iterable
    {
        yield 'Россия' => ['+7 (999) 123-45-67', PhoneDriverEnum::RU, '+79991234567', '+7 (999) 123-45-67'];
        yield 'Казахстан' => ['+7 (701) 123-45-67', PhoneDriverEnum::KZ, '+77011234567', '+7 (701) 123-45-67'];
        yield 'Беларусь' => ['+375 29 123-45-67', PhoneDriverEnum::BY, '+375291234567', '+375 (29) 123-45-67'];
        yield 'Армения' => ['+374 91 123456', PhoneDriverEnum::AM, '+37491123456', '+374 (91) 123-456'];
        yield 'Азербайджан' => ['+994 50 123 45 67', PhoneDriverEnum::AZ, '+994501234567', '+994 (50) 123-45-67'];
        yield 'Китай' => ['+86 138 1234 5678', PhoneDriverEnum::CN, '+8613812345678', '+86 138 1234 5678'];
    }

    /**
     * Проверим, что номер с кодом страны разбирается без подсказки: страна определяется по коду (для +7 — по коду
     * оператора), сохраняется E.164, выводится по маске страны.
     *
     * @see PhoneNumber::parse()
     * @see PhoneNumber::e164()
     * @see PhoneNumber::format()
     */
    #[Test]
    #[DataProvider('internationalNumbers')]
    public function parsesInternationalNumberAndDetectsCountry(string $raw, PhoneDriverEnum $country, string $e164, string $formatted): void
    {
        $phone = PhoneNumber::parse($raw);

        self::assertNotNull($phone);
        self::assertSame($country, $phone->country);
        self::assertSame($e164, $phone->e164());
        self::assertSame($formatted, $phone->format());
    }

    /**
     * Проверим, что национальный номер без кода разбирается по предпочтительной стране — выбранной в форме.
     *
     * @see PhoneNumber::parse()
     */
    #[Test]
    public function parsesNationalNumberByPreferredCountry(): void
    {
        self::assertSame('+8613812345678', PhoneNumber::parse('138 1234 5678', PhoneDriverEnum::CN)?->e164());
    }

    /**
     * Проверим, что российский номер с 8 и без кода страны по умолчанию — Россия.
     *
     * @see PhoneNumber::parse()
     */
    #[Test]
    public function parsesRussianTrunkPrefixByDefault(): void
    {
        self::assertSame('+79991234567', PhoneNumber::parse('8 (999) 123-45-67')?->e164());
    }

    /**
     * Проверим, что номер с «+» и чужим кодом не разбирается как национальный номер выбранной страны.
     *
     * @see PhoneNumber::parse()
     */
    #[Test]
    public function keepsExplicitDialCodeOverPreferredCountry(): void
    {
        self::assertSame(PhoneDriverEnum::BY, PhoneNumber::parse('+375291234567', PhoneDriverEnum::RU)?->country);
    }

    /**
     * Проверим, что сохранённый E.164 разбирается обратно в ту же страну и национальный номер.
     *
     * @see PhoneNumber::parse()
     */
    #[Test]
    public function parsesStoredE164Back(): void
    {
        $phone = PhoneNumber::parse('+8613812345678');

        self::assertSame([PhoneDriverEnum::CN, '13812345678'], [$phone?->country, $phone?->nationalNumber]);
    }

    /**
     * Проверим, что страны вне списка допустимых не определяются.
     *
     * @see PhoneNumber::parse()
     */
    #[Test]
    public function ignoresCountriesOutsideCandidates(): void
    {
        self::assertNull(PhoneNumber::parse('+8613812345678', candidates: [PhoneDriverEnum::RU, PhoneDriverEnum::KZ]));
    }

    /**
     * Проверим, что пустой и неверный номер не разбираются.
     *
     * @see PhoneNumber::parse()
     */
    #[Test]
    public function rejectsEmptyAndInvalidNumbers(): void
    {
        self::assertSame([null, null], [PhoneNumber::parse('  '), PhoneNumber::parse('+86 238 1234 5678')]);
    }
}
