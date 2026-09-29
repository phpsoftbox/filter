<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

enum PhoneDriverEnum: string
{
    case AM = 'am';
    case AZ = 'az';
    case BY = 'by';
    case RU = 'ru';
    case KZ = 'kz';

    /**
     * @param bool $mobileOnly Только мобильные номера. Сейчас влияет на RU: городские коды (3xx, 4xx, 8xx)
     *                         принимаются при false. Драйверы AM, AZ, BY, KZ проверяют свой список кодов всегда.
     */
    public function create(bool $mobileOnly = true): PhoneCountryDriverInterface
    {
        return match ($this) {
            self::AM => new PhoneAmDriver(),
            self::AZ => new PhoneAzDriver(),
            self::BY => new PhoneByDriver(),
            self::KZ => new PhoneKzDriver(),
            self::RU => new PhoneRuDriver($mobileOnly),
        };
    }
}
