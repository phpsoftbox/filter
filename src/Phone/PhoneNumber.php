<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone;

use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;

use function array_filter;
use function in_array;
use function preg_replace;
use function str_starts_with;
use function trim;

/**
 * Телефон, разобранный по стране: страна, национальный номер, E.164 и форматирование по маске страны.
 *
 * Страна определяется по номеру: сначала предпочтительная (например, выбранная в форме), затем остальные в порядке
 * {@see PhoneDriverEnum::detectionOrder()}. Номера с общим кодом страны (+7: Россия и Казахстан) различаются кодами
 * операторов.
 *
 * <code>
 * PhoneNumber::parse('8 (999) 123-45-67')?->e164();                  // '+79991234567'
 * PhoneNumber::parse('138 1234 5678', PhoneDriverEnum::CN)?->format(); // '+86 138 1234 5678'
 * PhoneNumber::parse('+77011234567')?->country;                      // PhoneDriverEnum::KZ
 * </code>
 */
final readonly class PhoneNumber
{
    private function __construct(
        public PhoneDriverEnum $country,
        public string $nationalNumber,
    ) {
    }

    /**
     * @param list<PhoneDriverEnum>|null $candidates Допустимые страны; null — все
     * @param bool $mobileOnly Только мобильные номера (см. {@see PhoneDriverEnum::create()})
     */
    public static function parse(
        ?string $raw,
        ?PhoneDriverEnum $preferred = null,
        ?array $candidates = null,
        bool $mobileOnly = true,
    ): ?self {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $order = PhoneDriverEnum::detectionOrder();
        if ($preferred !== null) {
            $order = [$preferred, ...array_filter($order, static fn (PhoneDriverEnum $country): bool => $country !== $preferred)];
        }

        foreach ($order as $country) {
            if ($candidates !== null && !in_array($country, $candidates, true)) {
                continue;
            }
            // Номер с «+» и чужим кодом страны не разбирается как национальный: +375… не станет номером России.
            if (str_starts_with(trim($raw), '+') && !self::hasDialCode($raw, $country)) {
                continue;
            }
            $result = $country->create($mobileOnly)->format($raw, prepareForDb: true, withCountryCode: false);
            if ($result->isValid()) {
                return new self($country, $result->getValue());
            }
        }

        return null;
    }

    /**
     * E.164: «+», код страны и национальный номер — формат хранения.
     */
    public function e164(): string
    {
        return '+' . $this->country->dialCode() . $this->nationalNumber;
    }

    /**
     * Номер по маске страны: +7 (999) 123-45-67, +86 138 1234 5678.
     */
    public function format(bool $withCountryCode = true): string
    {
        return $this->country->create(mobileOnly: false)
            ->format($this->nationalNumber, prepareForDb: false, withCountryCode: $withCountryCode)
            ->getValue();
    }

    private static function hasDialCode(string $raw, PhoneDriverEnum $country): bool
    {
        return str_starts_with(preg_replace('/\D/', '', $raw) ?? '', $country->dialCode());
    }
}
