<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use InvalidArgumentException;
use PhpSoftBox\Filter\Exception\NullValueNotAllowedException;
use PhpSoftBox\Filter\FilterAdapter;
use PhpSoftBox\Filter\IntegerFilter;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function trim;

#[CoversClass(FilterAdapter::class)]
#[CoversClass(NullValueNotAllowedException::class)]
final class FilterAdapterTest extends TestCase
{
    #[Test]
    public function intAndIntOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(12, $adapter->intOrNull('12'));
        self::assertNull($adapter->intOrNull('x'));
        self::assertSame(7, $adapter->int('7'));
    }

    #[Test]
    public function intThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected int value, got null.');
        $adapter->int(null);
    }

    #[Test]
    public function stringAndStringOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame('hello', $adapter->stringOrNull('  hello  '));
        self::assertNull($adapter->stringOrNull('   '));
        self::assertSame('42', $adapter->string(42));
    }

    #[Test]
    public function stringThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected string value, got null.');
        $adapter->string('   ');
    }

    #[Test]
    public function floatAndFloatOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(3.5, $adapter->floatOrNull('3.5'));
        self::assertNull($adapter->floatOrNull('x'));
        self::assertSame(8.0, $adapter->float('8'));
    }

    #[Test]
    public function floatThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected float value, got null.');
        $adapter->float(null);
    }

    #[Test]
    public function boolAndBoolOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertTrue($adapter->boolOrNull('yes'));
        self::assertFalse($adapter->boolOrNull('0'));
        self::assertNull($adapter->boolOrNull('unknown'));
        self::assertTrue($adapter->bool('1'));
    }

    #[Test]
    public function boolThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected bool value, got null.');
        $adapter->bool('unknown');
    }

    #[Test]
    public function phoneAndPhoneOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame('9991234567', $adapter->phoneOrNull('+7 (999) 123-45-67'));
        self::assertNull($adapter->phoneOrNull('123'));
        self::assertSame('9991234567', $adapter->phone('+7 (999) 123-45-67'));
        self::assertSame('77123456', $adapter->phone('+374 (77) 123-456', PhoneDriverEnum::AM));
    }

    #[Test]
    public function phoneThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected phone value, got null.');
        $adapter->phone('123');
    }

    #[Test]
    public function listAndListOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertNull($adapter->listOrNull(null));
        self::assertSame(['a', 'b'], $adapter->listOrNull(['a', '', 'b'], skipEmpty: true));
        self::assertSame([1, 2], $adapter->list([' 1 ', '2'], itemFilters: [static fn (mixed $v): mixed => trim((string) $v), new IntegerFilter()]));
    }

    #[Test]
    public function listThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected list value, got null.');
        $adapter->list(null);
    }

    #[Test]
    public function arrayAndArrayOrNullWork(): void
    {
        $adapter = new FilterAdapter();

        self::assertNull($adapter->arrayOrNull(null));
        self::assertSame(['a' => '1', 'b' => '2'], $adapter->array(['a' => '1', 'b' => '2']));
        self::assertSame([1, 2], $adapter->array([' 1 ', '2'], asList: true, itemFilters: [static fn (mixed $v): mixed => trim((string) $v), new IntegerFilter()]));
    }

    #[Test]
    public function arrayThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected array value, got null.');
        $adapter->array(null);
    }

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

    #[Test]
    public function jsonThrowsOnNullResult(): void
    {
        $adapter = new FilterAdapter();

        $this->expectException(NullValueNotAllowedException::class);
        $this->expectExceptionMessage('Expected json value, got null.');
        $adapter->json('{broken');
    }

    #[Test]
    public function applySupportsSingleAndPipelineFilters(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(10, $adapter->apply('10', new IntegerFilter()));
        self::assertSame(15, $adapter->apply(' 15 ', [static fn (mixed $v): mixed => trim((string) $v), new IntegerFilter()]));
    }

    #[Test]
    public function applyOrNullSwallowsFilterInvalidArgumentException(): void
    {
        $adapter = new FilterAdapter();
        $object  = new class () {
        };

        self::assertNull($adapter->applyOrNull($object, new IntegerFilter()));
    }

    #[Test]
    public function applyFilterClassWorksAndValidatesClassType(): void
    {
        $adapter = new FilterAdapter();

        self::assertSame(21, $adapter->applyFilterClass('21', IntegerFilter::class));

        $this->expectException(InvalidArgumentException::class);
        $adapter->applyFilterClass('21', self::class);
    }
}
