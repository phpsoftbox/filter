<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PhpSoftBox\Filter\Phone\PhoneNumber;

/**
 * Нормализует телефон любой поддержанной страны в E.164 (+79991234567, +8613812345678) — однозначный формат хранения.
 * Страна определяется по номеру; $country — предпочтительная страна для номера без кода (выбранная в форме).
 *
 * <code>
 * $filter = new InternationalPhoneFilter();
 * $filter('8 (999) 123-45-67');                                         // '+79991234567'
 * (new InternationalPhoneFilter(PhoneDriverEnum::CN))('138 1234 5678'); // '+8613812345678'
 * </code>
 */
final readonly class InternationalPhoneFilter implements FilterInterface
{
    /**
     * @param list<PhoneDriverEnum>|null $candidates Допустимые страны; null — все
     */
    public function __construct(
        private ?PhoneDriverEnum $country = null,
        private ?array $candidates = null,
        private bool $keepOriginalOnError = false,
        private bool $mobileOnly = true,
    ) {
    }

    public function __invoke(mixed $value): ?string
    {
        FilterValueGuard::guard($value, self::class);
        $raw = $value === null ? null : (string) $value;

        $phone = PhoneNumber::parse($raw, $this->country, $this->candidates, $this->mobileOnly);
        if ($phone instanceof PhoneNumber) {
            return $phone->e164();
        }

        return $this->keepOriginalOnError ? $raw : null;
    }
}
