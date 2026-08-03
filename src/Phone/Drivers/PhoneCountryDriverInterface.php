<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

use PhpSoftBox\Filter\Phone\PhoneValidationResult;

interface PhoneCountryDriverInterface
{
    /**
     * @param string|null $rawDigits Строка с цифрами, может содержать ведущий код страны
     */
    public function format(?string $rawDigits, bool $prepareForDb, bool $withCountryCode): PhoneValidationResult;
}
