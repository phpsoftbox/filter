<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

final class PhoneAzDriver extends PhoneDriverAbstract
{
    protected array $countryCodes = ['994', '0'];

    protected ?int $totalLength = 10;

    protected function getFormattedMask(bool $withCountryCode): string
    {
        if ($withCountryCode) {
            return '+994 ($1) $2-$3$4';
        }

        return '($1) $2-$3$4';
    }

    /**
     * @return array<int, string>
     */
    protected function getOperatorCodes(): array
    {
        $baseCodes = ['10', '50', '51', '55', '60', '70', '77', '99'];
        $codes     = [];

        foreach ($baseCodes as $baseCode) {
            for ($i = 0; $i <= 9; $i++) {
                $codes[] = $baseCode . (string) $i;
            }
        }

        return $codes;
    }
}
