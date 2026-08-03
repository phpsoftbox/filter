<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use function preg_replace;

/**
 * Выполняет замену по регулярному выражению.
 *
 * Пример:
 * <code>
 * $filter = new PregReplaceFilter('/\\D+/', '');
 * $filter('1a2b3'); // '123'
 * </code>
 */
final readonly class PregReplaceFilter implements FilterInterface
{
    public function __construct(
        private string $pattern,
        private string $replacement,
        private int $limit = -1,
    ) {
    }

    public function __invoke(mixed $value): string
    {
        $string = FilterValueGuard::toStringValue($value, self::class);
        $result = preg_replace($this->pattern, $this->replacement, $string, $this->limit);

        return $result === null ? $string : $result;
    }
}
