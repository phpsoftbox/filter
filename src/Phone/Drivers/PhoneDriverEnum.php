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

    public function create(): PhoneCountryDriverInterface
    {
        return match ($this) {
            self::AM => new PhoneAmDriver(),
            self::AZ => new PhoneAzDriver(),
            self::BY => new PhoneByDriver(),
            self::KZ => new PhoneKzDriver(),
            self::RU => new PhoneRuDriver(),
        };
    }
}
