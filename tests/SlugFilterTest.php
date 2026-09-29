<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Tests;

use PhpSoftBox\Filter\SlugFilter;
use PhpSoftBox\Inflector\InflectorFactory;
use PhpSoftBox\Inflector\LanguageEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlugFilter::class)]
#[CoversMethod(SlugFilter::class, '__invoke')]
final class SlugFilterTest extends TestCase
{
    /**
     * Проверим, что по умолчанию кириллица транслитерируется, а не вырезается.
     *
     * @see SlugFilter::__invoke()
     */
    #[Test]
    public function transliteratesCyrillicByDefault(): void
    {
        $filter = new SlugFilter();

        self::assertSame('privet-mir-2024', $filter('Привет мир 2024'));
    }

    /**
     * Проверим, что slug по умолчанию совпадает с Inflector::urlize() для той же строки.
     *
     * @see SlugFilter::__invoke()
     */
    #[Test]
    public function matchesInflectorUrlize(): void
    {
        $filter    = new SlugFilter();
        $inflector = InflectorFactory::create(LanguageEnum::RU);
        $value     = 'Щука, Ёжик & Café — склад №1!';

        self::assertSame($inflector->urlize($value), $filter($value));
    }

    /**
     * Проверим, что с allowUnicode кириллица сохраняется и приводится к нижнему регистру unicode-aware.
     *
     * @see SlugFilter::__invoke()
     */
    #[Test]
    public function keepsUnicodeLettersInLowercase(): void
    {
        $filter = new SlugFilter(allowUnicode: true);

        self::assertSame('привет-мир-2024', $filter('Привет, МИР 2024'));
    }

    /**
     * Проверим, что lowercase: false сохраняет регистр, а свой разделитель схлопывается и обрезается.
     *
     * @see SlugFilter::__invoke()
     */
    #[Test]
    public function keepsCaseWithCustomSeparator(): void
    {
        $filter = new SlugFilter(separator: '_', lowercase: false);

        self::assertSame('Sklad_No1', $filter(' Склад -- №1 '));
    }
}
