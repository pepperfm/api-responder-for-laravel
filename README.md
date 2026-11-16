# API Responder for Laravel

Small, predictable JSON API responses via Laravel dependency injection or a facade.

> **Requirements:** PHP **8.5** and Laravel **13.x** only.
> This package formats outgoing HTTP responses; it is **not** a DTO/validation library such as spatie/laravel-data.

## Installation

```bash
composer require pepperfm/api-responder-for-laravel
```

Laravel auto-discovers the provider. To customize the envelope keys, publish the config:

```bash
php artisan vendor:publish --provider="Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider" --tag="config"
```


## Dependency injection

```php
use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

final class UserController
{
    public function __construct(private ResponseContract $json) {}

    public function index(): JsonResponse
    {
        return $this->json->response([['id' => 1]]);
    }

    public function show(): JsonResponse
    {
        return $this->json->withDataKey('user')->response(['id' => 1]);
    }
}
```

The default success envelope is:

```json
{
    "entities": [{"id": 1}],
    "meta": [],
    "message": "Success"
}
```

Direct `response()` uses the configured plural key. Use `withDataKey()` or `forMethod()` to select a different key explicitly.

## Response methods

```php
return $this->json->response(['id' => 1]);              // 200 + envelope
return $this->json->stored(['id' => 1]);                // 201 + envelope
return $this->json->deleted();                         // 204, no body
return $this->json->error('Not found', 404);            // 404 + message/errors
return $this->json->raw(['id' => 1]);                   // 200, payload as-is
return $this->json->withDataKey('user')->response($data);
return $this->json->forMethod('show')->response($data); // singular "entity" key
```

Payloads may be PHP arrays or Laravel `Arrayable` objects, including Collections. Existing Eloquent models should usually be transformed with a resource or explicit DTO first to control which fields are exposed.

### Fluent builder and unwrapped responses

```php
return $this->json->build()
    ->withDataKey('user')
    ->response(['id' => 1]);

return $this->json->build()
    ->withoutWrapping()
    ->response(['id' => 1]); // {"id":1,"meta":[],"message":"Success"}
```

Unwrapped responses must contain an associative array and may **not** contain `meta` or `message` keys: those belong to the envelope. Attempting to overwrite them throws `InvalidArgumentException`. For an exact JSON payload with no envelope (including root lists or reserved keys), use `raw()`.

### PHP attributes (optional)

```php
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;

#[ResponseDataKey('user')]
public function show(): JsonResponse
{
    return $this->json->fromAction()->response(['id' => 1]);
}

#[WithoutWrapping]
public function stats(): JsonResponse
{
    return $this->json->fromAction()->response(['active' => 5]);
}
```

The optional `fromAction()` uses reflection and caller detection. For services or non-controller code, supply the class and method explicitly.

## Pagination

Three Laravel paginators are supported: `paginate()`, `simplePaginate()` and `cursorPaginate()`.

```php
return $this->json->paginated(User::query()->paginate(20));

// Transform the returned data but keep the original paging metadata:
$paginator = User::query()->paginate(20);
$items = $paginator->getCollection()->map(fn ($user) => [
    'id' => $user->id,
    'name' => $user->name,
]);
return $this->json->paginated($items, $paginator);
```

The response shape is:

```json
{
    "entities": [{"id": 1, "name": "John"}],
    "meta": {
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "from": 1,
            "to": 1,
            "prev_page_url": null,
            "next_page_url": null,
            "last_page": 1,
            "total": 1,
            "links": []
        }
    },
    "message": "Success"
}
```

For simple pagination, `last_page`, `total`, and `links` are omitted. Cursor pagination instead includes `next_cursor`, `prev_cursor` and cursor navigation URLs.

## Configuration

| Key | Default | Meaning |
| --- | --- | --- |
| `plural_data_key` | `entities` | Direct response and collection key |
| `singular_data_key` | `entity` | Key selected by the REST convention |
| `using_for_rest` | `true` | Enable `forMethod()` convention |
| `methods_for_singular_key` | `['show', 'update']` | Action names using the singular key |
| `without_wrapping` | `false` | Spread associative data into the envelope root |

No middleware is installed by default and incoming `Accept` headers are not modified. JSON responses have a standard outgoing `Content-Type` header.

## Local development

There is no GitHub Actions workflow. Run checks locally using PHP 8.5:

```bash
composer install
composer test
composer lint
composer test-coverage
```


See [CHANGELOG.md](CHANGELOG.md) for breaking changes from 3.x.
