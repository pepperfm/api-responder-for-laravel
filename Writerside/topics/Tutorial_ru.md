# Стандартизация JSON-ответов Laravel без трейтов

API Responder нужен для **форматирования HTTP-ответов**, а не для валидации, DTO или преобразования моделей. Его задача — дать контроллерам небольшой, предсказуемый интерфейс.

**Требования:** PHP 8.5 и Laravel 13. Версия 3.x поддерживала более старые окружения; текущая разработка ориентирована только на Laravel 13.

## Установка

```bash
composer require pepperfm/api-responder-for-laravel
```

## Простой пример

```php
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

final class UserController
{
    public function __construct(private ResponseContract $json) {}

    public function index()
    {
        return $this->json->response(
            User::query()->get(['id', 'name'])
        );
    }

    public function show(User $user)
    {
        return $this->json->withDataKey('user')->response([
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return $this->json->deleted();
    }
}
```

Ответ `index()` — обычный JSON без дополнительного `response.data` внутри тела:

```json
{
  "entities": [{"id": 1, "name": "Anna"}],
  "meta": [],
  "message": "Success"
}
```

`deleted()` возвращает статус **204 No Content** с пустым телом. Если требуется вернуть JSON-сообщение об удалении, используйте ответ 200.

## Пагинация

```php
return $this->json->paginated(User::query()->paginate());
return $this->json->paginated(User::query()->simplePaginate());
return $this->json->paginated(User::query()->cursorPaginate());

// Отдельно отформатировать записи и сохранить метаданные:
$paginator = User::query()->paginate();
$data = $paginator->getCollection()->map(
    fn ($user) => ['id' => $user->id, 'name' => $user->name],
);

return $this->json->paginated($data, $paginator);
```

Информация о пагинации помещается в `meta.pagination`. Набор ключей зависит от типа paginator: обычный, простой и cursor используют разные метаданные.

## Дополнительные способы вызова

```php
return $this->json->stored(['id' => 42]); // 201
return $this->json->error('Not found', 404);
return $this->json->raw(['message' => 'Custom']); // Точное тело JSON
return $this->json->forMethod('show')->response(['id' => 1]);
```

`response()` и `raw()` принимают массивы и объекты Laravel `Arrayable`, включая `Collection`. Для явного контроля отображаемых полей используйте API Resource или DTO.

## Атрибуты и обёртка

```php
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;

#[ResponseDataKey('user')]
public function show(User $user)
{
    return $this->json->fromAction()->response(['id' => $user->id]);
}

#[WithoutWrapping]
public function stats()
{
    return $this->json->fromAction()->response(['active' => 10]);
}
```

Без обёртки `meta` и `message` остаются зарезервированными ключами. При конфликте выбрасывается исключение — данные не перезаписываются молча. Для произвольного JSON и списков в корне используйте `raw()`.

Пакет **не изменяет входящий `Accept`** и не добавляет middleware автоматически. Тесты выполняются локально командами `composer test` и `composer lint`.

Полное описание: [README](https://github.com/pepperfm/api-responder-for-laravel/blob/3.x/README.md).
