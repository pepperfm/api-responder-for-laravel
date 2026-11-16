# API Responder 4.x — configurable Laravel JSON responses without traits

**PHP 8.5 and Laravel 13.** The configurable envelope, REST data-key naming, PHP attributes, fluent builder, facade and DI are retained.

This is a response formatter, not a DTO, validation or API Resource replacement.

## Dependency injection

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
        return $this->json->forMethod('show')->response(['id' => $user->id]);
    }
}
```

Direct `response()` uses the default `entities` key. An explicit REST context, e.g. `forMethod('show')`, uses `entity`. Both keys and the REST actions are configurable.

## Custom key and attributes

```php
return $this->json->withDataKey('users')->response($users);

use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;

#[ResponseDataKey('profile')]
public function show(User $user)
{
    return $this->json->fromAction()->response(['id' => $user->id]);
}
```

`fromAction()` reads attributes on the calling method; it supports facade invocations. You can pass an explicit class and method if needed.

`#[WithoutWrapping]` or `build()->withoutWrapping()` spreads associative response data into the root. Reserved `meta` / `message` keys raise an error rather than being silently replaced; use `raw()` for an exact payload.

## Response methods and pagination

```php
return $this->json->response($data);      // 200
return $this->json->stored($data);        // 201
return $this->json->deleted();           // 204, empty body
return $this->json->error('Not found', 404);
return $this->json->raw(['id' => 1]);    // exact JSON
return $this->json->paginated(User::paginate());
```

Length-aware, simple and cursor pagination are supported. Laravel itself supplies the pagination metadata under `meta.pagination`. You can pass a transformed Collection alongside the original paginator.

Checks are local (`composer test`, `composer lint`). No GitHub Actions workflow is included.

See the [README](https://github.com/pepperfm/api-responder-for-laravel#readme).
