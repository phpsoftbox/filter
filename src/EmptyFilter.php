<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function array_values;
use function is_array;
use function is_string;
use function trim;

/**
 * Удаляет пустые элементы из массива.
 *
 * removeNull:
 * - удаляет только null.
 *
 * removeEmpty:
 * - дополнительно удаляет пустые строки.
 */
final readonly class EmptyFilter implements FilterInterface
{
    public function __construct(
        private bool $removeNull = true,
        private bool $removeEmpty = false,
        private bool $trimStrings = true,
    ) {
    }

    public function __invoke(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        $items  = is_array($value) ? $value : [$value];
        $result = [];

        foreach ($items as $item) {
            if ($item === null && $this->removeNull) {
                continue;
            }

            if ($this->removeEmpty && is_string($item)) {
                $string = $this->trimStrings ? trim($item) : $item;
                if ($string === '') {
                    continue;
                }
            }

            $result[] = $item;
        }

        return array_values($result);
    }
}
