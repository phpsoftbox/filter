# Фильтры

Фильтры помогают привести входные данные к нужному виду перед валидацией.
Их можно использовать как кастеры: `BooleanFilter`, `IntegerFilter`, `FloatFilter` возвращают соответствующий тип.
Они используются через `Request::filter()` из компонента `phpsoftbox/request`.

## Пример

```php
use PhpSoftBox\Filter\TrimFilter;
use PhpSoftBox\Filter\PhoneFilter;

$this->request->filter([
    'login' => [new TrimFilter()],
    'phone' => [new TrimFilter(), new PhoneFilter()],
]);
```

## Встроенные фильтры

- `TrimFilter` — `trim()` строки.
- `DefaultFilter` — подставляет default для `null` и `''`.
- `LowercaseFilter` — приводит строку к нижнему регистру.
- `UppercaseFilter` — приводит строку к верхнему регистру.
- `NullIfEmptyFilter` — превращает пустую строку в `null`.
- `StrReplaceFilter` — `str_replace()`/`str_ireplace()`.
- `PregReplaceFilter` — `preg_replace()`.
- `DigitsFilter` — оставляет только цифры.
- `SlugFilter` — формирует slug из строки; кириллица транслитерируется так же, как `Inflector::urlize()`.
- `ExplodeFilter` — превращает строку в массив через `explode()`.
- `ArrayFilter` — приводит значение к массиву (`->asList()` включает режим списка 0..N).
- `ListFilter` — алиас для `ArrayFilter->asList()`.
- `EmptyFilter` — удаляет из массива `null` и/или пустые строки.
- `JsonDecodeFilter` — декодирует JSON в массив/объект.
- `DateTimeFilter` — нормализует дату/время в заданный формат (int — timestamp, строка — дата).
- `PhoneFilter` — нормализация телефона (драйверы AM/AZ/BY/KZ/RU, проверка длины/кода оператора).
- `BooleanFilter` — приводит к `bool` (true/false/null).
- `IntegerFilter` — приводит к `int`; дробные значения и значения вне диапазона `int` — default (без усечения).
- `FloatFilter` — приводит к `float`.

## Как добавить свой фильтр

Фильтр — это invokable‑класс:

```php
final class SlugFilter
{
    public function __invoke(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }
}
```

Лучше реализовать `PhpSoftBox\Filter\FilterInterface`. `FilterAdapter::apply()`/`applyOrNull()` принимают
фильтр, `Closure` или список из них: массив всегда считается списком фильтров, поэтому callable-массив
(`[$obj, 'method']`) и строки-функции (`'trim'`) не принимаются — элемент списка другого типа даёт `LogicException`
(ошибка конфигурации, `applyOrNull()` её не подавляет). Метод или функцию оборачивайте в `Closure`:
`$obj->method(...)`, `trim(...)`.

```php
use PhpSoftBox\Filter\FilterAdapter;
use PhpSoftBox\Filter\TrimFilter;

$value = new FilterAdapter()->apply(' 42 ', [new TrimFilter(), $normalizer->normalize(...)]);
```

## Примеры

Нормализация строки:

```php
use PhpSoftBox\Filter\TrimFilter;
use PhpSoftBox\Filter\LowercaseFilter;

$this->request->filter([
    'email' => [new TrimFilter(), new LowercaseFilter()],
]);
```

Приведение к массиву:

```php
use PhpSoftBox\Filter\ArrayFilter;
use PhpSoftBox\Filter\EmptyFilter;
use PhpSoftBox\Filter\ExplodeFilter;
use PhpSoftBox\Filter\ListFilter;
use PhpSoftBox\Filter\TrimFilter;
use PhpSoftBox\Filter\IntegerFilter;

$this->request->filter([
    'tags' => [new ExplodeFilter(','), new ListFilter()],
    'ids' => [
        new ExplodeFilter(','),
        (new ArrayFilter(skipEmpty: true))->asList()->filters(new TrimFilter(), new IntegerFilter()),
        new EmptyFilter(removeNull: true),
    ],
]);
```

Поэлементная фильтрация списка:

```php
use PhpSoftBox\Filter\ListFilter;
use PhpSoftBox\Filter\EmptyFilter;
use PhpSoftBox\Filter\ExplodeFilter;
use PhpSoftBox\Filter\TrimFilter;
use PhpSoftBox\Filter\IntegerFilter;

$this->request->filter([
    'ids' => [
        new ExplodeFilter(','),
        (new ListFilter(skipEmpty: true))->filters(new TrimFilter(), new IntegerFilter()),
        new EmptyFilter(removeNull: true),
    ],
]);
```

JSON → массив:

```php
use PhpSoftBox\Filter\JsonDecodeFilter;

$this->request->filter([
    'payload' => [new JsonDecodeFilter()],
]);
```

## SlugFilter

По умолчанию строка транслитерируется в ASCII через `PhpSoftBox\Inflector\Transliterator` — тот же, что в
`Inflector::urlize()`, поэтому `new SlugFilter()` и `urlize()` дают одинаковый результат. Буквы и цифры
сохраняются, остальные символы заменяются разделителем, повторы разделителя схлопываются.

```php
use PhpSoftBox\Filter\SlugFilter;

new SlugFilter()('Привет мир 2024');                  // 'privet-mir-2024'
new SlugFilter()('Щука & Café');                      // 'shchuka-cafe'
new SlugFilter(separator: '_', lowercase: false)('Склад №1'); // 'Sklad_No1'
```

С `allowUnicode: true` буквы любых алфавитов сохраняются, нижний регистр — через `mb_strtolower()`:

```php
$this->request->filter([
    'title' => [new SlugFilter(allowUnicode: true)], // 'Привет, МИР' → 'привет-мир'
]);
```

## IntegerFilter

Возвращает `int` только если значение представимо без потерь, иначе — `default` (по умолчанию `null`):

| Вход | Результат |
|---|---|
| `42`, `'42'`, `' -42 '`, `'+7'`, `'007'` | `42`, `42`, `-42`, `7`, `7` |
| `2.0` | `2` |
| `1.9`, `'1.5'`, `-1.5` | default (дробная часть) |
| `'99999999999999999999'`, `1e30`, `NAN`, `INF` | default (вне диапазона `int`) |
| `'1e3'`, `'abc'`, `''`, `null` | default |

Значение никогда не усекается и не округляется: если нужно округление — примените его явно до фильтра.

## DateTimeFilter

- `DateTimeInterface` — используется как есть;
- `int` — Unix timestamp;
- строка — разбирается `DateTimeImmutable` в `timezone`, в том числе числовая: `'20240115'` — 15 января 2024;
- числовая строка как timestamp (например, `?from=1700000000` из query string) — только с
  `numericStringAsTimestamp: true`;
- ошибка разбора — `null` (или исходное значение с `returnOriginalOnError: true`).

Дата/время в ISO:

Дата/время в ISO:

```php
use PhpSoftBox\Filter\DateTimeFilter;

$this->request->filter([
    'published_at' => [new DateTimeFilter(format: 'Y-m-d\\TH:i:sP', timezone: 'UTC')],
    'from'         => [new DateTimeFilter(format: 'Y-m-d', numericStringAsTimestamp: true)],
]);
```

## PhoneFilter

`PhoneFilter` использует драйверы стран (`PhoneDriverEnum`) и умеет:
- удалять все нецифровые символы;
- снимать код страны (только если номер длиннее национального, так что `800 123-45-67` не теряет первую цифру);
- проверять длину и коды операторов;
- возвращать форматированный или «сырой» номер для БД.

| Драйвер | Код страны | Национальный номер | Принимаемые коды |
|---|---|---|---|
| `RU` | `+7`, `8` | 10 цифр | мобильные `9xx`; с `mobileOnly: false` — также городские/бесплатные `3xx`, `4xx`, `8xx` |
| `KZ` | `+7` | 10 цифр | `7xx` |
| `BY` | `+375`, `80`, `0` | 9 цифр | мобильные `25`, `29`, `33`, `44` и `17` (Минск) |
| `AZ` | `+994`, `0` | 9 цифр | `10`, `50`, `51`, `55`, `60`, `70`, `77`, `99` |
| `AM` | `+374`, `0` | 8 цифр | `33`, `41`, `43`, `44`, `55`, `77`, `88`, `91`, `93`–`99` |

Номер, не прошедший проверку, даёт `null` (или исходную строку с `keepOriginalOnError: true`). Причину можно
получить через `$filter->validate($raw)` (`PhoneValidationResult`).

Пример:

```php
use PhpSoftBox\Filter\PhoneFilter;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;

$filter = new PhoneFilter(
    driver: PhoneDriverEnum::RU,
    prepareForDb: true,
    withCountryCode: false,
);

$filter('+7 (999) 123-45-67'); // '9991234567'
$filter('+7 (495) 123-45-67'); // null — городской номер

$landline = new PhoneFilter(mobileOnly: false);
$landline('+7 (495) 123-45-67'); // '4951234567'
```
