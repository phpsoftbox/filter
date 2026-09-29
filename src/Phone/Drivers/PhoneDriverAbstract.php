<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

use PhpSoftBox\Filter\Phone\PhoneValidationResult;

use function in_array;
use function preg_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

abstract class PhoneDriverAbstract implements PhoneCountryDriverInterface
{
    /**
     * Коды страны.
     *
     * @var array<int, string>
     */
    protected array $countryCodes = [];

    /**
     * Коды операторов внутри страны.
     *
     * @var array<int, string>
     */
    protected array $operatorCodes = [];

    protected ?int $totalLength = null;

    public function format(?string $rawDigits, bool $prepareForDb, bool $withCountryCode): PhoneValidationResult
    {
        if (!$rawDigits) {
            return PhoneValidationResult::makeError(PhoneValidationResult::ERROR_PHONE_EMPTY);
        }

        $digits = preg_replace('/\D/', '', $rawDigits) ?? '';
        if ($digits === '') {
            return PhoneValidationResult::makeError(PhoneValidationResult::ERROR_PHONE_EMPTY);
        }

        // Код страны снимается, только если номер длиннее национального: иначе номер вида 800 123-45-67
        // потерял бы первую цифру.
        $nationalLength = $this->totalLength > 0 ? $this->totalLength - 1 : null;
        if ($nationalLength === null || strlen($digits) > $nationalLength) {
            foreach ($this->countryCodes as $code) {
                if (str_starts_with($digits, $code)) {
                    $digits = substr($digits, strlen($code));
                    break;
                }
            }
        }

        if (strlen($digits) >= 3) {
            $operator = substr($digits, 0, 3);

            $allowed = $this->operatorCodes;
            if ($allowed === []) {
                $allowed = $this->getOperatorCodes();
            }

            if (!in_array($operator, $allowed, true)) {
                return PhoneValidationResult::makeError(PhoneValidationResult::ERROR_INCORRECT_OPERATOR_CODE);
            }
        }

        if ($this->totalLength > 0 && strlen($digits) !== $this->totalLength - 1) {
            return PhoneValidationResult::makeError(PhoneValidationResult::ERROR_INVALID_LENGTH);
        }

        if ($prepareForDb) {
            return PhoneValidationResult::makeSuccess($digits);
        }

        $pattern   = '/(\d{3})?(\d{3})?(\d{2})?(\d{2})?/';
        $mask      = $this->getFormattedMask($withCountryCode);
        $formatted = preg_replace($pattern, $mask, $digits, 1) ?? '';

        return PhoneValidationResult::makeSuccess(trim($formatted));
    }

    abstract protected function getFormattedMask(bool $withCountryCode): string;

    /**
     * Вернуть список допустимых кодов операторов, если не задано явно в $operatorCodes.
     *
     * @return array<int, string>
     */
    protected function getOperatorCodes(): array
    {
        return [];
    }
}
