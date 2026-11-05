<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

enum PhoneDriverEnum: string
{
    case AM = 'am';
    case AZ = 'az';
    case BY = 'by';
    case CN = 'cn';
    case RU = 'ru';
    case KZ = 'kz';

    /**
     * @param bool $mobileOnly Только мобильные номера. Сейчас влияет на RU: городские коды (3xx, 4xx, 8xx)
     *                         принимаются при false. Драйверы AM, AZ, BY, CN, KZ проверяют свой список кодов всегда.
     */
    public function create(bool $mobileOnly = true): PhoneCountryDriverInterface
    {
        return match ($this) {
            self::AM => new PhoneAmDriver(),
            self::AZ => new PhoneAzDriver(),
            self::BY => new PhoneByDriver(),
            self::CN => new PhoneCnDriver(),
            self::KZ => new PhoneKzDriver(),
            self::RU => new PhoneRuDriver($mobileOnly),
        };
    }

    /**
     * Международный код страны без «+»: 7, 86, 374…
     */
    public function dialCode(): string
    {
        return match ($this) {
            self::AM           => '374',
            self::AZ           => '994',
            self::BY           => '375',
            self::CN           => '86',
            self::KZ, self::RU => '7',
        };
    }

    /**
     * Порядок определения страны по номеру без явной страны: сначала Россия (у неё и у Казахстана общий код +7, их
     * различают коды операторов), затем остальные.
     *
     * @return list<self>
     */
    public static function detectionOrder(): array
    {
        return [self::RU, self::KZ, self::BY, self::AZ, self::AM, self::CN];
    }
}
