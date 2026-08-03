<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function array_values;
use function is_array;

/**
 * Нормализует входные данные в массив.
 *
 * Пример:
 * <code>
 * $filter = new ArrayFilter(skipEmpty: true);
 * $filter(['a', '', null, 'b']); // ['a', 'b']
 * </code>
 */
final readonly class ArrayFilter implements FilterInterface
{
    /**
     * @var array<int, callable(mixed): mixed>
     */
    private array $itemFilters;

    public function __construct(
        private bool $skipEmpty = false,
        array $itemFilters = [],
        private bool $asList = false,
    ) {
        $this->itemFilters = $itemFilters;
    }

    /**
     * Добавляет цепочку фильтров, применяемых к каждому элементу массива.
     */
    public function filters(callable ...$filters): self
    {
        return new self(
            skipEmpty: $this->skipEmpty,
            itemFilters: [...$this->itemFilters, ...$filters],
            asList: $this->asList,
        );
    }

    /**
     * Включает/выключает режим списка (переиндексация ключей 0..N).
     */
    public function asList(bool $asList = true): self
    {
        return new self(
            skipEmpty: $this->skipEmpty,
            itemFilters: $this->itemFilters,
            asList: $asList,
        );
    }

    public function __invoke(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            $result = $this->applyItemFilters($value);

            return $this->asList ? array_values($result) : $result;
        }

        $result = $this->applyItemFilters([$value]);

        return $this->asList ? array_values($result) : $result;
    }

    /**
     * @param array<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    private function applyItemFilters(array $items): array
    {
        if ($this->itemFilters === [] && !$this->skipEmpty) {
            return $items;
        }

        $result = [];
        foreach ($items as $key => $item) {
            $normalized = $item;
            foreach ($this->itemFilters as $filter) {
                $normalized = $filter($normalized);
            }
            if ($this->skipEmpty && ($normalized === null || $normalized === '')) {
                continue;
            }
            $result[$key] = $normalized;
        }

        return $result;
    }
}
