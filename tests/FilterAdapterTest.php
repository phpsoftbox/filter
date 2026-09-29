<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use InvalidArgumentException;
use PhpSoftBox\Filter\Exception\NullValueNotAllowedException;
use PhpSoftBox\Filter\FilterAdapter;
use PhpSoftBox\Filter\IntegerFilter;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function trim;

#[CoversClass(FilterAdapter::class)]
#[CoversClass(NullValueNotAllowedException::class)]
#[CoversMethod(FilterAdapter::class, 'int')]
#[CoversMethod(FilterAdapter::class, 'intOrNull')]
#[CoversMethod(FilterAdapter::class, 'string')]
#[CoversMethod(FilterAdapter::class, 'stringOrNull')]
#[CoversMethod(FilterAdapter::class, 'float')]
#[CoversMethod(FilterAdapter::class, 'floatOrNull')]
#[CoversMethod(FilterAdapter::class, 'bool')]
#[CoversMethod(FilterAdapter::class, 'boolOrNull')]
#[CoversMethod(FilterAdapter::class, 'phone')]
#[CoversMethod(FilterAdapter::class, 'phoneOrNull')]
#[CoversMethod(FilterAdapter::class, 'list')]
#[CoversMethod(FilterAdapter::class, 'listOrNull')]
#[CoversMethod(FilterAdapter::class, 'array')]
#[CoversMethod(FilterAdapter::class, 'arrayOrNull')]
#[CoversMethod(FilterAdapter::class, 'json')]
#[CoversMethod(FilterAdapter::class, 'jsonOrNull')]
#[CoversMethod(FilterAdapter::class, 'apply')]
#[CoversMethod(FilterAdapter::class, 'applyOrNull')]
#[CoversMethod(FilterAdapter::class, 'applyFilterClass')]
final class FilterAdapterTest extends TestCase
{
    /**
     * Проверим, что int()/intOrNull() приводят строку к int, а нераспознанное значение даёт null.
     *
     * @see FilterAdapter::int()
     * @see FilterAdapter::intOrNull()
     */
    #[Test]
    public function intAndIntOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(12, $adapter->intOrNull('12'));
        self::assertNull($adapter->intOrNull('x'));
        self::assertSame(7, $adapter->int('7'));
    }

    /**
     * Проверим, что int() бросает NullValueNotAllowedException для null.
     *
     * @see FilterAdapter::int()
     */
    #[Test]
    public function intThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected int value, got null.');
        $adapter->int(null);
    }

    /**
     * Проверим, что string()/stringOrNull() обрезают пробелы, а пустая строка даёт null.
     *
     * @see FilterAdapter::string()
     * @see FilterAdapter::stringOrNull()
     */
    #[Test]
    public function stringAndStringOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame('hello', $adapter->stringOrNull('  hello  '));
        self::assertNull($adapter->stringOrNull('   '));
        self::assertSame('42', $adapter->string(42));
    }

    /**
     * Проверим, что string() бросает NullValueNotAllowedException для пустой строки.
     *
     * @see FilterAdapter::string()
     */
    #[Test]
    public function stringThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected string value, got null.');
        $adapter->string('   ');
    }

    /**
     * Проверим, что float()/floatOrNull() приводят строку к float.
     *
     * @see FilterAdapter::float()
     * @see FilterAdapter::floatOrNull()
     */
    #[Test]
    public function floatAndFloatOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(3.5, $adapter->floatOrNull('3.5'));
        self::assertNull($adapter->floatOrNull('x'));
        self::assertSame(8.0, $adapter->float('8'));
    }

    /**
     * Проверим, что float() бросает NullValueNotAllowedException для null.
     *
     * @see FilterAdapter::float()
     */
    #[Test]
    public function floatThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected float value, got null.');
        $adapter->float(null);
    }

    /**
     * Проверим, что bool()/boolOrNull() распознают булевы строки.
     *
     * @see FilterAdapter::bool()
     * @see FilterAdapter::boolOrNull()
     */
    #[Test]
    public function boolAndBoolOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertTrue($adapter->boolOrNull('yes'));
        self::assertFalse($adapter->boolOrNull('0'));
        self::assertNull($adapter->boolOrNull('unknown'));
        self::assertTrue($adapter->bool('1'));
    }

    /**
     * Проверим, что bool() бросает NullValueNotAllowedException для нераспознанного значения.
     *
     * @see FilterAdapter::bool()
     */
    #[Test]
    public function boolThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected bool value, got null.');
        $adapter->bool('unknown');
    }

    /**
     * Проверим, что phone()/phoneOrNull() нормализуют номер через драйвер страны.
     *
     * @see FilterAdapter::phone()
     * @see FilterAdapter::phoneOrNull()
     */
    #[Test]
    public function phoneAndPhoneOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame('9991234567', $adapter->phoneOrNull('+7 (999) 123-45-67'));
        self::assertNull($adapter->phoneOrNull('123'));
        self::assertSame('9991234567', $adapter->phone('+7 (999) 123-45-67'));
        self::assertSame('77123456', $adapter->phone('+374 (77) 123-456', PhoneDriverEnum::AM));
    }

    /**
     * Проверим, что phone() бросает NullValueNotAllowedException для некорректного номера.
     *
     * @see FilterAdapter::phone()
     */
    #[Test]
    public function phoneThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected phone value, got null.');
        $adapter->phone('123');
    }

    /**
     * Проверим, что list()/listOrNull() строят список и применяют поэлементные фильтры.
     *
     * @see FilterAdapter::list()
     * @see FilterAdapter::listOrNull()
     */
    #[Test]
    public function listAndListOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertNull($adapter->listOrNull(null));
        self::assertSame(['a', 'b'], $adapter->listOrNull(['a', '', 'b'], skipEmpty: true));
        self::assertSame([1, 2], $adapter->list([' 1 ', '2'], itemFilters: [static fn (mixed $v): mixed => trim((string) $v), new IntegerFilter()]));
    }

    /**
     * Проверим, что list() бросает NullValueNotAllowedException для null.
     *
     * @see FilterAdapter::list()
     */
    #[Test]
    public function listThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected list value, got null.');
        $adapter->list(null);
    }

    /**
     * Проверим, что array()/arrayOrNull() приводят значение к массиву и применяют поэлементные фильтры.
     *
     * @see FilterAdapter::array()
     * @see FilterAdapter::arrayOrNull()
     */
    #[Test]
    public function arrayAndArrayOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertNull($adapter->arrayOrNull(null));
        self::assertSame(['a' => '1', 'b' => '2'], $adapter->array(['a' => '1', 'b' => '2']));
        self::assertSame([1, 2], $adapter->array([' 1 ', '2'], asList: true, itemFilters: [static fn (mixed $v): mixed => trim((string) $v), new IntegerFilter()]));
    }

    /**
     * Проверим, что array() бросает NullValueNotAllowedException для null.
     *
     * @see FilterAdapter::array()
     */
    #[Test]
    public function arrayThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected array value, got null.');
        $adapter->array(null);
    }

    /**
     * Проверим, что json()/jsonOrNull() декодируют JSON.
     *
     * @see FilterAdapter::json()
     * @see FilterAdapter::jsonOrNull()
     */
    #[Test]
    public function jsonAndJsonOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertNull($adapter->jsonOrNull(null));
        self::assertSame(['a' => 1], $adapter->jsonOrNull('{"a":1}'));
        self::assertSame(['a' => 1], $adapter->json('{"a":1}'));
        self::assertNull($adapter->jsonOrNull('{broken'));
        self::assertSame(['fallback' => true], $adapter->jsonOrNull('{broken', default: ['fallback' => true]));
    }

    /**
     * Проверим, что json() бросает NullValueNotAllowedException для null.
     *
     * @see FilterAdapter::json()
     */
    #[Test]
    public function jsonThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected json value, got null.');
        $adapter->json('{broken');
    }

    /**
     * Проверим, что apply() принимает один фильтр и список фильтров (FilterInterface и Closure).
     *
     * @see FilterAdapter::apply()
     */
    #[Test]
    public function applySupportsSingleAndPipelineFilters(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(10, $adapter->apply('10', new IntegerFilter()));
        self::assertSame(15, $adapter->apply(' 15 ', [static fn (mixed $v): mixed => trim((string) $v), new IntegerFilter()]));
    }

    /**
     * Проверим, что applyOrNull() превращает InvalidArgumentException фильтра в null.
     *
     * @see FilterAdapter::applyOrNull()
     */
    #[Test]
    public function applyOrNullSwallowsFilterInvalidArgumentException(): void
    {
        $adapter = new FilterAdapter();
        $object  = new class () {
        };

        self::assertNull($adapter->applyOrNull($object, new IntegerFilter()));
    }

    /**
     * Проверим, что applyFilterClass() создаёт фильтр по классу и отклоняет класс без FilterInterface.
     *
     * @see FilterAdapter::applyFilterClass()
     */
    #[Test]
    public function applyFilterClassWorksAndValidatesClassType(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(21, $adapter->applyFilterClass('21', IntegerFilter::class));

        $this->expectException(InvalidArgumentException::class);
        $adapter->applyFilterClass('21', self::class);
    }
}
