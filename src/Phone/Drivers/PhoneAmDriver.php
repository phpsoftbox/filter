<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

final class PhoneAmDriver extends PhoneDriverAbstract
{
    protected array $countryCodes = ['374', '0'];

    protected ?int $totalLength = 9;

    protected function getFormattedMask(bool $withCountryCode): string
    {
        if ($withCountryCode) {
            return '+374 ($1) $2-$3$4';
        }

        return '($1) $2-$3$4';
    }

    /**
     * @return array<int, string>
     */
    protected function getOperatorCodes(): array
    {
        $baseCodes = ['33', '41', '43', '44', '55', '77', '88', '91', '93', '94', '95', '96', '97', '98', '99'];
        $codes     = [];

        foreach ($baseCodes as $baseCode) {
            for ($i = 0; $i <= 9; $i++) {
                $codes[] = $baseCode . (string) $i;
            }
        }

        return $codes;
    }
}
