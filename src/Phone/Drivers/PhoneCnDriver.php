<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

/**
 * Китай: +86, мобильные номера — 11 цифр, начинаются с 1 (коды операторов 130–199). Формат: +86 138 1234 5678.
 *
 * Принимаются только мобильные номера: городские номера Китая разной длины по регионам.
 */
final class PhoneCnDriver extends PhoneDriverAbstract
{
    protected array $countryCodes = ['0086', '86'];

    protected ?int $totalLength = 12;

    protected string $dialCode = '86';

    protected string $formatPattern = '/(\d{3})(\d{4})(\d{4})/';

    protected function getFormattedMask(bool $withCountryCode): string
    {
        if ($withCountryCode) {
            return '+86 $1 $2 $3';
        }

        return '$1 $2 $3';
    }

    /**
     * @return array<int, string>
     */
    protected function getOperatorCodes(): array
    {
        $codes = [];
        for ($i = 130; $i <= 199; $i++) {
            $codes[] = (string) $i;
        }

        return $codes;
    }
}
