<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

/**
 * Нормализует список (переиндексирует массив).
 *
 * Внутри делегирует в ArrayFilter->asList().
 *
 * Пример:
 * <code>
 * $filter = new ListFilter(skipEmpty: true);
 * $filter([2 => 'first', 5 => '', 8 => 'second']); // ['first', 'second']
 * </code>
 */
final class ListFilter implements FilterInterface
{
    /**
     * @var array<int, callable(mixed): mixed>
     */
    private array $itemFilters;

    /**
     * @param array<int, callable(mixed): mixed> $itemFilters
     */
    public function __construct(
        private bool $skipEmpty = false,
        array $itemFilters = [],
    ) {
        $this->itemFilters = $itemFilters;
    }

    /**
     * Добавляет цепочку фильтров, применяемых к каждому элементу списка.
     */
    public function filters(callable ...$filters): self
    {
        return new self(
            skipEmpty: $this->skipEmpty,
            itemFilters: [...$this->itemFilters, ...$filters],
        );
    }

    public function __invoke(mixed $value): array
    {
        return new ArrayFilter(
            skipEmpty: $this->skipEmpty,
            itemFilters: $this->itemFilters,
        )->asList()($value);
    }
}
