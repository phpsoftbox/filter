<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use LogicException;
use PhpSoftBox\Filter\FilterAdapter;
use PhpSoftBox\Filter\TrimFilter;
use PhpSoftBox\Filter\UppercaseFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterAdapter::class)]
#[CoversMethod(FilterAdapter::class, 'apply')]
#[CoversMethod(FilterAdapter::class, 'applyOrNull')]
final class FilterAdapterPipelineTest extends TestCase
{
    /**
     * Проверим, что callable-массив [$obj, 'method'] не разбирается как список фильтров: это ошибка конфигурации.
     *
     * @see FilterAdapter::apply()
     */
    #[Test]
    public function applyRejectsCallableArray(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(LogicException::class);

        $adapter->apply(' x ', [new TrimFilter(), '__invoke']);
    }

    /**
     * Проверим, что applyOrNull() не подавляет ошибку конфигурации списка фильтров.
     *
     * @see FilterAdapter::applyOrNull()
     */
    #[Test]
    public function applyOrNullDoesNotSwallowInvalidFilterList(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(LogicException::class);

        $adapter->applyOrNull(' x ', ['trim']);
    }

    /**
     * Проверим, что метод объекта передаётся как Closure (first-class callable) и применяется по порядку.
     *
     * @see FilterAdapter::apply()
     */
    #[Test]
    public function applyAcceptsFirstClassCallable(): void
    {
        $adapter = new FilterAdapter();
        $trim    = new TrimFilter();

        self::assertSame('X', $adapter->apply(' x ', [$trim->__invoke(...), new UppercaseFilter()]));
    }
}
