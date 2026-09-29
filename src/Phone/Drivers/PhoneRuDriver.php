<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone\Drivers;

/**
 * Россия: +7 / 8, 10 цифр национального номера.
 *
 * По умолчанию принимаются только мобильные номера (коды 9xx) — для входа по SMS, уведомлений и т.п.
 * С $mobileOnly = false принимаются и городские/бесплатные номера (коды 3xx, 4xx, 8xx).
 */
final class PhoneRuDriver extends PhoneDriverAbstract
{
    protected array $countryCodes = ['8', '7'];

    protected ?int $totalLength = 11;

    public function __construct(
        private readonly bool $mobileOnly = true,
    ) {
    }

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
        $ranges = $this->mobileOnly ? [[900, 999]] : [[300, 499], [800, 999]];

        $codes = [];
        foreach ($ranges as [$from, $to]) {
            for ($i = $from; $i <= $to; $i++) {
                $codes[] = (string) $i;
            }
        }

        return $codes;
    }
}
