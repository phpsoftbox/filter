# PhpSoftBox Filter

Независимый компонент для нормализации и преобразования значений.

## Установка

```bash
composer require phpsoftbox/filter
```

## Использование

Все фильтры являются вызываемыми объектами:

```php
use PhpSoftBox\Filter\BooleanFilter;
use PhpSoftBox\Filter\LowercaseFilter;
use PhpSoftBox\Filter\TrimFilter;

$value = new TrimFilter()('  value  ');
$value = new LowercaseFilter()($value);
$debug = new BooleanFilter(default: false)('true');
```

`FilterAdapter` предоставляет типизированные преобразования и выполнение pipeline:

```php
use PhpSoftBox\Filter\FilterAdapter;
use PhpSoftBox\Filter\IntegerFilter;
use PhpSoftBox\Filter\TrimFilter;

$filters = new FilterAdapter();
$port = $filters->int('8080');
$value = $filters->apply(' 42 ', [new TrimFilter(), new IntegerFilter()]);
```

`apply()` принимает фильтр, `Closure` или список из них; callable-массивы и строки-функции не принимаются —
оборачивайте их в `Closure` (`$obj->method(...)`).

Компонент также содержит фильтры массивов, JSON, дат, строк и телефонных номеров. Важные правила:
- `IntegerFilter` не усекает: дробное значение (`1.9`, `'1.5'`) и значение вне диапазона `int` дают default;
- `SlugFilter` транслитерирует кириллицу так же, как `Inflector::urlize()` (`'Привет мир'` → `'privet-mir'`);
- `DateTimeFilter` считает timestamp только `int`; числовую строку — с `numericStringAsTimestamp: true`;
- `PhoneFilter` для RU по умолчанию принимает только мобильные номера (`mobileOnly: false` — и городские).
Подробные примеры доступны в [docs/usage.md](docs/usage.md).

## Breaking change

Фильтры перенесены из `PhpSoftBox\Validator\Filter` в `PhpSoftBox\Filter`. Старые namespace
не поддерживаются.

## Лицензия

MIT
