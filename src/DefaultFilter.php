<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

/**
 * Возвращает значение по умолчанию для null и пустой строки.
 *
 * Пример:
 * <code>
 * $filter = new DefaultFilter('all');
 * $filter(null); // 'all'
 * $filter('');   // 'all'
 * </code>
 */
final readonly class DefaultFilter implements FilterInterface
{
    public function __construct(
        private mixed $default = null,
    ) {
    }

    public function __invoke(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $this->default;
        }

        return $value;
    }
}
