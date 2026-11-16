# API Responder 4.x: Laravel 13 / PHP 8.5

Небольшой форматтер JSON-ответов. Один класс, четыре метода, никаких трейтов, ServiceProvider, фасадов, конфигурации или DTO.

```php
use Pepperfm\ApiResponder\JsonResponder;

final class UserController
{
    public function __construct(private JsonResponder $json) {}

    public function show(User $user)
    {
        return $this->json->response(['id' => $user->id]);
    }

    public function index()
    {
        return $this->json->paginated(User::query()->paginate());
    }

    public function store(StoreUserRequest $request)
    {
        return $this->json->created(
            User::create($request->validated())->only(['id', 'name']),
        );
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent(); // Laravel, не пакет
    }
}
```

Ответы имеют предсказуемую структуру: `{"data": ..., "meta": [], "message": "Success"}`. Для ошибок используйте `$json->error('Not found', 404)`.

Для `simplePaginate()` и `cursorPaginate()` действует тот же метод `paginated()`. Для преобразования элементов — `$paginator->through(fn ($model) => ...)`.

Для ответа без обёртки используйте родной `response()->json()`. В 4.x удалены старые `withDataKey()`, `forMethod()`, `fromAction()`, атрибуты, фасад, контракты, глобальные хелперы и методы `stored()` / `deleted()`.

Полная документация и миграция: [README](https://github.com/pepperfm/api-responder-for-laravel#readme).
