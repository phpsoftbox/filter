<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

final class PhoneByDriver extends PhoneDriverAbstract
{
    protected array $countryCodes = ['375', '80', '0'];

    protected ?int $totalLength = 10;

    protected function getFormattedMask(bool $withCountryCode): string
    {
        if ($withCountryCode) {
            return '+375 ($1) $2-$3$4';
        }

        return '($1) $2-$3$4';
    }

    /**
     * @return array<int, string>
     */
    protected function getOperatorCodes(): array
    {
        $baseCodes = ['17', '25', '29', '33', '44'];
        $codes     = [];

        foreach ($baseCodes as $baseCode) {
            for ($i = 0; $i <= 9; $i++) {
                $codes[] = $baseCode . (string) $i;
            }
        }

        return $codes;
    }
}
