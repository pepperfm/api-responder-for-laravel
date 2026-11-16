# API Responder 4.x: настраиваемые JSON-ответы без трейтов

**PHP 8.5 и Laravel 13.** В этой версии сохранились конфигурация, нейминг ответов, PHP-атрибуты, fluent builder, facade и DI.

Пакет форматирует исходящие ответы; валидацию и преобразование моделей оставляем FormRequest, DTO и Laravel Resources.

## Базовое использование

```php
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

final class UserController
{
    public function __construct(private ResponseContract $json) {}

    public function index()
    {
        return $this->json->response(User::query()->get(['id', 'name']));
    }

    public function show(User $user)
    {
        return $this->json->forMethod('show')->response(
            ['id' => $user->id, 'name' => $user->name],
        );
    }
}
```

Простой `response()` возвращает `{"entities": ..., "meta": [], "message": "Success"}`. Явный `forMethod('show')` переключает ключ на `entity`. Ключи и список REST-методов настраиваются в `config/laravel-api-responder.php`.

## Имена и атрибуты

```php
// Переименование ключа для отдельного ответа:
return $this->json->withDataKey('users')->response($users);

// Декларативная настройка:
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;

#[ResponseDataKey('profile')]
public function show(User $user)
{
    return $this->json->fromAction()->response(['id' => $user->id]);
}
```

При вызове `fromAction()` атрибуты определяются по вызывающему методу; работает и при использовании facade. Если нужна предсказуемость вне контроллера, передайте класс и метод явно.

Можно убрать обёртку через `#[WithoutWrapping]` или `build()->withoutWrapping()`. При конфликте ключей `meta` / `message` будет выброшено исключение вместо перезаписи данных. Для полностью произвольного JSON используйте `raw()`.

## Ответы и пагинация

```php
return $this->json->response($data);            // 200
return $this->json->stored($data);              // 201
return $this->json->deleted();                 // 204 без тела
return $this->json->error('Not found', 404);
return $this->json->raw(['id' => 1]);          // без обёртки
return $this->json->paginated(User::paginate()); // meta.pagination
```

Работают `paginate()`, `simplePaginate()` и `cursorPaginate()`. Метаданные формируются самим Laravel. Для отдельно преобразованной коллекции можно передать исходный paginator вторым аргументом.

Проверки выполняются локально через `composer test` и `composer lint`. GitHub Actions отсутствует.

Подробности и миграция: [README](https://github.com/pepperfm/api-responder-for-laravel#readme).
