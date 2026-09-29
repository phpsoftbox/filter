<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use PhpSoftBox\Filter\Phone\Drivers\PhoneCountryDriverInterface;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PhpSoftBox\Filter\Phone\PhoneValidationResult;

/**
 * Нормализует телефонные номера через драйвер страны ({@see PhoneDriverEnum}: AM, AZ, BY, KZ, RU).
 * Для RU по умолчанию принимаются только мобильные номера; городские — с mobileOnly: false.
 *
 * Пример:
 * <code>
 * $filter = new PhoneFilter();
 * $filter('+7 (999) 123-45-67'); // '9991234567'
 * </code>
 */
final class PhoneFilter implements FilterInterface
{
    private PhoneCountryDriverInterface $driver;
    private readonly PhoneDriverEnum $driverEnum;

    /**
     * @param bool $mobileOnly Только мобильные номера (для RU — коды 9xx; false — также городские 3xx, 4xx, 8xx)
     */
    public function __construct(
        PhoneDriverEnum $driver = PhoneDriverEnum::RU,
        private readonly bool $prepareForDb = true,
        private readonly bool $withCountryCode = false,
        private readonly bool $keepOriginalOnError = false,
        private readonly bool $mobileOnly = true,
    ) {
        $this->driverEnum = $driver;
        $this->driver     = $driver->create($mobileOnly);
    }

    public function switchPrepareForDbTo(bool $value): self
    {
        if ($this->prepareForDb === $value) {
            return $this;
        }

        return new self(
            driver: $this->driverEnum,
            prepareForDb: $value,
            withCountryCode: $this->withCountryCode,
            keepOriginalOnError: $this->keepOriginalOnError,
            mobileOnly: $this->mobileOnly,
        );
    }

    public function __invoke(mixed $value): ?string
    {
        FilterValueGuard::guard($value, self::class);
        $raw    = $value === null ? null : (string) $value;
        $result = $this->validate($raw);

        if ($result->isValid()) {
            return $result->getValue();
        }

        return $this->keepOriginalOnError ? $raw : null;
    }

    public function validate(?string $raw): PhoneValidationResult
    {
        return $this->driver->format($raw, $this->prepareForDb, $this->withCountryCode);
    }
}
