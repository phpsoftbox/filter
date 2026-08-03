<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

final class PhoneRuDriver extends PhoneDriverAbstract
{
    protected array $countryCodes = ['8', '7'];

    protected ?int $totalLength = 11;

    protected function getFormattedMask(bool $withCountryCode): string
    {
        if ($withCountryCode) {
            return '+7 ($1) $2-$3$4';
        }

        return '($1) $2-$3$4';
    }

    /**
     * @return array<int, string>
     */
    protected function getOperatorCodes(): array
    {
        $codes = [];
        for ($i = 900; $i <= 999; $i++) {
            $codes[] = (string) $i;
        }

        return $codes;
    }
}
