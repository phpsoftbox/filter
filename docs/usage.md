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
- `SlugFilter` — формирует slug из строки.
- `ExplodeFilter` — превращает строку в массив через `explode()`.
- `ArrayFilter` — приводит значение к массиву (`->asList()` включает режим списка 0..N).
- `ListFilter` — алиас для `ArrayFilter->asList()`.
- `EmptyFilter` — удаляет из массива `null` и/или пустые строки.
- `JsonDecodeFilter` — декодирует JSON в массив/объект.
- `DateTimeFilter` — нормализует дату/время в заданный формат.
- `PhoneFilter` — нормализация телефона (драйверы RU/KZ, проверка длины/оператора).
- `BooleanFilter` — приводит к `bool` (true/false/null).
- `IntegerFilter` — приводит к `int`.
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

Можно реализовать `PhpSoftBox\Filter\FilterInterface`, но это не обязательно — достаточно callable.

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

Slug с Unicode:

```php
use PhpSoftBox\Filter\SlugFilter;

$this->request->filter([
    'title' => [new SlugFilter(allowUnicode: true)],
]);
```

Дата/время в ISO:

```php
use PhpSoftBox\Filter\DateTimeFilter;

$this->request->filter([
    'published_at' => [new DateTimeFilter(format: 'Y-m-d\\TH:i:sP', timezone: 'UTC')],
]);
```

## PhoneFilter

`PhoneFilter` использует драйверы стран (RU/KZ) и умеет:
- удалять все нецифровые символы;
- проверять длину и коды операторов;
- возвращать форматированный или «сырой» номер для БД.

Пример:

```php
use PhpSoftBox\Filter\PhoneFilter;
use PhpSoftBox\Filter\Phone\Drivers\PhoneDriverEnum;

$filter = new PhoneFilter(
    driver: PhoneDriverEnum::RU,
    prepareForDb: true,
    withCountryCode: false,
);
```
