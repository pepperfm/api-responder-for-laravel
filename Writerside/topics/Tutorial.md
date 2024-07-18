# Стандартизация ответов API без трейтов

### Проблема
Заметил я, что б**о**льшая часть библиотек, созданных для апи респонса, реализованы через трейты, остальная часть — огромные библиотеки.
В этих трейтах реализованы методы под всё, что только можно (response, accepted, created, forbidden...)

Таким образом, если в моём контроллере 1-2 метода, то, подключая такой трейт, я имею в классе кучу ненужного мусора.
В паре больших библиотек на 700+ звёзд я вижу overengineering на уровне UX (для себя, как для пользователя библиотеки)

### Что делать?
Написать свою библиотеку!

Я решил создать такую логику обработки данных, чтобы на пользовательском уровне требовалось:
- минимум действий
- имелась простота использования
- читаемость

То есть для получение стандартизированного респонса, всё, что нам нужно, это вернуть респонс через объект библиотеки

```bash
composer require pepperfm/api-responder-for-laravel
```

Итого, базовый минимум, который у нас есть сразу после установки библиотеки, это:

Успешный ответ:
```json
{
  response: {
    data: {
      entities,
      meta: []|{},
      message: 'Success'
    }
  }
}
```
Ответ с ошибкой:
```json
{
  response: {
    data: {
      errors: null,
      message: 'Error'
    }
  }
}
```

```php
public function __construct(public ResponseContract $json)
{
}

public function index(Request $request)
{
    $users = User::query()->get();

    return $this->json->response($users);
}

public function store(UserService $service)
{
    try {
        app('db')->beginTransaction();
        
        $service->update(request()->input());
        
        app('db')->commit();
    } catch (\Exception $e) {
        app('db')->rollback();
        logger()->debug($e->getMessage());

        return $this->json->error(
            message: $e->getMessage(),
            httpStatusCode: $e->getCode()
        );
    }


    return $this->json->response($users);
}
```

На выходе при успешном ответе имеем распаковку формата в виде: `response.data.entities`
По-умолчанию формат актуален в контексте REST, то есть для методов `show()` и `update()` ответ будет формата: `response.data.entity`
___
## Глубокое погружение
Конечно, для любителей кастомизации и копания в конфигах я так же создал песочницу кода, с которым можно поиграться

### Возможности
#### Любимый нами сахар

Обёртка над `response()` метдом для пагинации:
```php
/*
 * Generate response.data.meta.pagination from first argument of `pagiated()` method
 */
public function index(Request $request)
{
    $users = User::query()->paginate();

    return $this->json->paginated($users);
}
```
Метод `paginated()` принимает два основных параметра:
```php
array|\Illuminate\Pagination\LengthAwarePaginator $data,
array|\Illuminate\Pagination\LengthAwarePaginator $meta = [],
```
В своей логике резолвит их и добавляет в ответ по ключу `meta` — ключ `pagination`

Интерфейсы ответа в соответствии с форматом, возвращаемым Laravel:
```php
export interface IPaginatedResponse<T> {
    current_page: number
    per_page: number
    last_page: number
    data: T[]
    from: number
    to: number
    total: number
    prev_page_url?: any
    next_page_url: string
    links: IPaginatedResponseLinks[]
}
export interface IPaginatedResponseLinks {
    url?: any
    label: string
    active: boolean
}
```
В итоге ответ получается формата:
```php
{
  response: {
    data: {
      entities,
      meta: {
        pagination: ...
      },
      message: 'Success'
    }
  }
}
```


Обёртка над `response()` метдом для кодов ответа:
```php
public function store(UserService $service)
{
    ...
    // message: 'Stored', httpStatusCode: JsonResponse::HTTP_CREATED
    return $this->json->stored();
}

public function destroy()
{
    ...
    // message: 'Deleted', httpStatusCode: JsonResponse::HTTP_NO_CONTENT
    return $this->json->deleted();
}
```

#### Работа с разными типами параметра
Первый аргумент метода `response()` может быть типов `array|Arrayable`, поэтому можно маппить данные перед передачей в метод в рамках этих типов. К примеру:
```php
public function index()
{
    $users = User::query()->paginate();
    $dtoCollection = $users->getCollection()->mapInto(UserDto::class);

    return resolve(ResponseContract::class)->paginated(
        data: $dtoCollection,
        meta: $users
    );
}
```
```php
public function index()
{
    $users = SpatieUserData::collect(User::query()->get());

    return \ApiBaseResponder::response($users);
}
```

#### Кастомизация через конфиг
Сам конфиг:
```php
return [
    'plural_data_key' => 'entities',

    'singular_data_key' => 'entity',

    'using_for_rest' => true,

    'methods_for_singular_key' => ['show', 'update'],

    'force_json_response_header' => true,
];
```
- отключение `using_for_rest` оставляет возвращаемый формат всегда `response.data.entities` (мн. ч.) не зависимо от метода, из которого происходит вызов
- с помощью `methods_for_singular_key` можно пополнить список методов, в которых будет возвращаться ключ в ед. ч.
- `methods_for_singular_key`, собственно, добавляет заголовок в запросы по классике: `$request->headers->set('Accept', 'application/json');`

#### Кастомизация через атрибуты
Блокировка значений `using_for_rest` и `methods_for_singular_key` в конфиге для установки ключа ответа в соответствии с `singular_data_key`
```php
#[ResponseDataKey]
public function attributeWithoutParam(): JsonResponse
{
    // response.data.entity
    return BaseResponse::response($this->user);
}
```
По аналогии можно передать своё назавние ключа
```php
#[ResponseDataKey('random_key')]
public function attributeWithParam(): JsonResponse
{
    // response.data.random_key
    return BaseResponse::response($this->user);
}
```

### В итоге
Осноанвя потребность закрыта: я хотел иметь возможность просто поставить библиотеку, и просто иметь из коробки лаконичный базис для стандартизации формата ответа. Без лишних движений.

И, конечно, как возможностей для лишних движений (кастомизация), так и ненасыпанного сахара — ещё очень много, так что в доработке библиотеки всё впереди) но основной посыл я точно сохраню! Ибо, как гласят великие мемы истории:

> Красота в простате