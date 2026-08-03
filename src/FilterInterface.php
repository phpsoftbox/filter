<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter;

interface FilterInterface
{
    public function __invoke(mixed $value): mixed;
}
