<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

use Closure;
use InvalidArgumentException;
use LogicException;
use PhpSoftBox\Filter\Exception\NullValueNotAllowedException;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;

use function array_values;
use function get_debug_type;
use function is_a;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

final readonly class FilterAdapter
{
    private IntegerFilter $integerFilter;
    private FloatFilter $floatFilter;
    private BooleanFilter $booleanFilter;
    private StringFilter $stringFilter;

    public function __construct()
    {
        $this->integerFilter = new IntegerFilter();

        $this->floatFilter = new FloatFilter();

        $this->booleanFilter = new BooleanFilter();

        $this->stringFilter = new StringFilter();
    }

    public static function make(): self
    {
        return new self();
    }

    /**
     * Применяет фильтр или цепочку фильтров. Функция-фильтр передаётся только как Closure: массив всегда
     * считается списком фильтров, поэтому callable-массив ([$obj, 'method']) и строки-функции не принимаются —
     * оберните их в Closure (`$obj->method(...)`, `trim(...)`).
     *
     * @param FilterInterface|Closure(mixed):mixed|list<FilterInterface|Closure(mixed):mixed> $filters
     * @throws LogicException Если элемент списка не FilterInterface и не Closure.
     */
    public function apply(mixed $value, FilterInterface|Closure|array $filters): mixed
    {
        foreach ($this->normalizeFilters($filters) as $filter) {
            $value = $filter($value);
        }

        return $value;
    }

    /**
     * Как apply(), но InvalidArgumentException фильтра превращается в null. Неверный список фильтров
     * (элемент не FilterInterface и не Closure) — ошибка конфигурации, она не подавляется.
     *
     * @param FilterInterface|Closure(mixed):mixed|list<FilterInterface|Closure(mixed):mixed> $filters
     */
    public function applyOrNull(mixed $value, FilterInterface|Closure|array $filters): mixed
    {
        try {
            return $this->apply($value, $filters);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @param class-string<FilterInterface> $filterClass
     */
    public function applyFilterClass(mixed $value, string $filterClass, mixed ...$constructorArgs): mixed
    {
        if (!is_a($filterClass, FilterInterface::class, true)) {
            throw new InvalidArgumentException('Filter class must implement FilterInterface.');
        }

        /** @var FilterInterface $filter */
        $filter = new $filterClass(...$constructorArgs);

        return $this->apply($value, $filter);
    }

    public function intOrNull(mixed $value): ?int
    {
        $casted = $this->applyOrNull($value, $this->integerFilter);

        return is_int($casted) ? $casted : null;
    }

    public function int(mixed $value): int
    {
        $casted = $this->intOrNull($value);
        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected int value, got null.');
        }

        return $casted;
    }

    public function floatOrNull(mixed $value): ?float
    {
        $casted = $this->applyOrNull($value, $this->floatFilter);

        return is_float($casted) ? $casted : null;
    }

    public function float(mixed $value): float
    {
        $casted = $this->floatOrNull($value);
        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected float value, got null.');
        }

        return $casted;
    }

    public function boolOrNull(mixed $value): ?bool
    {
        $casted = $this->applyOrNull($value, $this->booleanFilter);

        return is_bool($casted) ? $casted : null;
    }

    public function bool(mixed $value): bool
    {
        $casted = $this->boolOrNull($value);
        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected bool value, got null.');
        }

        return $casted;
    }

    public function stringOrNull(mixed $value): ?string
    {
        $casted = $this->applyOrNull($value, $this->stringFilter);

        return is_string($casted) ? $casted : null;
    }

    public function string(mixed $value): string
    {
        $casted = $this->stringOrNull($value);
        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected string value, got null.');
        }

        return $casted;
    }

    public function phoneOrNull(
        mixed $value,
        PhoneDriverEnum $driver = PhoneDriverEnum::RU,
        bool $prepareForDb = true,
        bool $withCountryCode = false,
        bool $keepOriginalOnError = false,
        bool $mobileOnly = true,
    ): ?string {
        $casted = $this->applyOrNull(
            $value,
            new PhoneFilter(
                driver: $driver,
                prepareForDb: $prepareForDb,
                withCountryCode: $withCountryCode,
                keepOriginalOnError: $keepOriginalOnError,
                mobileOnly: $mobileOnly,
            ),
        );

        return is_string($casted) ? $casted : null;
    }

    public function phone(
        mixed $value,
        PhoneDriverEnum $driver = PhoneDriverEnum::RU,
        bool $prepareForDb = true,
        bool $withCountryCode = false,
        bool $keepOriginalOnError = false,
        bool $mobileOnly = true,
    ): string {
        $casted = $this->phoneOrNull(
            value: $value,
            driver: $driver,
            prepareForDb: $prepareForDb,
            withCountryCode: $withCountryCode,
            keepOriginalOnError: $keepOriginalOnError,
            mobileOnly: $mobileOnly,
        );

        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected phone value, got null.');
        }

        return $casted;
    }

    /**
     * @param list<callable(mixed):mixed> $itemFilters
     * @return list<mixed>|null
     */
    public function listOrNull(
        mixed $value,
        bool $skipEmpty = false,
        array $itemFilters = [],
    ): ?array {
        if ($value === null || $value === '') {
            return null;
        }

        $filter = new ListFilter(
            skipEmpty: $skipEmpty,
            itemFilters: $itemFilters,
        );

        $casted = $this->applyOrNull($value, $filter);

        return is_array($casted) ? $casted : null;
    }

    /**
     * @param list<callable(mixed):mixed> $itemFilters
     * @return list<mixed>
     */
    public function list(
        mixed $value,
        bool $skipEmpty = false,
        array $itemFilters = [],
    ): array {
        $casted = $this->listOrNull($value, $skipEmpty, $itemFilters);

        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected list value, got null.');
        }

        return $casted;
    }

    /**
     * @param list<callable(mixed):mixed> $itemFilters
     * @return array<array-key, mixed>|null
     */
    public function arrayOrNull(
        mixed $value,
        bool $skipEmpty = false,
        array $itemFilters = [],
        bool $asList = false,
    ): ?array {
        if ($value === null || $value === '') {
            return null;
        }

        $filter = new ArrayFilter(
            skipEmpty: $skipEmpty,
            itemFilters: $itemFilters,
            asList: $asList,
        );

        $casted = $this->applyOrNull($value, $filter);

        return is_array($casted) ? $casted : null;
    }

    /**
     * @param list<callable(mixed):mixed> $itemFilters
     * @return array<array-key, mixed>
     */
    public function array(
        mixed $value,
        bool $skipEmpty = false,
        array $itemFilters = [],
        bool $asList = false,
    ): array {
        $casted = $this->arrayOrNull($value, $skipEmpty, $itemFilters, $asList);
        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected array value, got null.');
        }

        return $casted;
    }

    public function jsonOrNull(
        mixed $value,
        bool $assoc = true,
        int $depth = 512,
        int $flags = 0,
        mixed $default = null,
        bool $returnOriginalOnError = false,
    ): mixed {
        if ($value === null || $value === '') {
            return null;
        }

        $filter = new JsonDecodeFilter(
            assoc: $assoc,
            depth: $depth,
            flags: $flags,
            default: $default,
            returnOriginalOnError: $returnOriginalOnError,
        );

        return $this->applyOrNull($value, $filter);
    }

    public function json(
        mixed $value,
        bool $assoc = true,
        int $depth = 512,
        int $flags = 0,
        mixed $default = null,
        bool $returnOriginalOnError = false,
    ): mixed {
        $casted = $this->jsonOrNull(
            value: $value,
            assoc: $assoc,
            depth: $depth,
            flags: $flags,
            default: $default,
            returnOriginalOnError: $returnOriginalOnError,
        );

        if ($casted === null) {
            throw new NullValueNotAllowedException('Expected json value, got null.');
        }

        return $casted;
    }

    /**
     * @param FilterInterface|Closure(mixed):mixed|list<FilterInterface|Closure(mixed):mixed> $filters
     * @return list<FilterInterface|Closure(mixed):mixed>
     */
    private function normalizeFilters(FilterInterface|Closure|array $filters): array
    {
        if (!is_array($filters)) {
            return [$filters];
        }

        foreach ($filters as $index => $filter) {
            if (!$filter instanceof FilterInterface && !$filter instanceof Closure) {
                throw new LogicException(sprintf(
                    'Filter #%s must be %s or Closure, %s given.',
                    (string) $index,
                    FilterInterface::class,
                    get_debug_type($filter),
                ));
            }
        }

        return array_values($filters);
    }
}
