# API Responder for Laravel

A small **configurable JSON response formatter** for Laravel. Consistent API envelopes, REST-aware key names, PHP attributes, and paginator metadata — without replacing your DTO or resource layer.

> **4.x requires PHP 8.5 and Laravel 13.** This release intentionally drops backward compatibility with older runtime versions, but preserves the useful responder features.

## Installation

```bash
composer require pepperfm/api-responder-for-laravel
```

Laravel auto-discovers the package provider. To customize the response keys:

```bash
php artisan vendor:publish --provider="Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider" --tag="config"
```

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
        return $this->json->forMethod('show')->response([
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }
}
```

By default, `response()` returns:

```json
{
  "entities": [{"id": 1, "name": "Anna"}],
  "meta": [],
  "message": "Success"
}
```

With the explicit `forMethod('show')` context, it instead uses `entity`. A plain `response()` **does not infer the caller method**: this avoids reflection on ordinary responses. All keys are configurable.

## Key naming and attributes

There are three ways to select the response data key:

```php
// Explicit name (highest priority):
return $this->json->withDataKey('users')->response($users);

// REST convention from config:
return $this->json->forMethod('show')->response($user);

// PHP attribute on the controller method (opt-in):
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;

#[ResponseDataKey('profile')]
public function show(User $user)
{
    return $this->json->fromAction()->response(['id' => $user->id]);
}
```

The final example uses `profile` instead of `entity`. Attributes work via normal DI and the `BaseResponse` facade. When calling `fromAction()` from another class, provide explicit class/method arguments if the caller cannot be inferred reliably:

```php
$this->json->fromAction(UserController::class, 'show')->response($data);
```

The `#[WithoutWrapping]` attribute and fluent `withoutWrapping()` method also remain available:

```php
return $this->json->build()->withoutWrapping()->response(['active' => 3]);
// {"active":3,"meta":[],"message":"Success"}
```

To avoid silent data loss, unwrapped payloads cannot use reserved keys `meta` or `message`, and cannot be root lists. Use `raw()` when you want an exact payload.

## Response methods

```php
return $this->json->response($data);                    // 200, configurable envelope
return $this->json->stored($data);                      // 201, configurable envelope
return $this->json->deleted();                         // 204, empty body
return $this->json->error('Not found', 404);            // error envelope
return $this->json->raw(['message' => 'Untouched']);    // exact JSON, no envelope
return $this->json->withDataKey('user')->response($data);
```

The main payload argument accepts arrays and Laravel `Arrayable` implementations such as Collections. For Eloquent models, select safe fields with a Resource or DTO before serialization.

### Pagination

Works with `paginate()`, `simplePaginate()`, and `cursorPaginate()`:

```php
return $this->json->paginated(User::query()->paginate(20));
```

The payload appears under the configured data key; pagination metadata is placed under `meta.pagination`. Native Laravel paginator metadata is used as-is (except for moving `data` to the response payload key), rather than maintaining a separate list of fields.

To map records while retaining the paginator's metadata:

```php
$paginator = User::query()->paginate(20);
$items = $paginator->getCollection()->map(fn (User $user) => [
    'id' => $user->id,
    'name' => $user->name,
]);

return $this->json->paginated($items, $paginator);
```

Or use Laravel's `$paginator->through(fn ($user) => ...)` and pass the paginator directly.

## Configuration

Published file: `config/laravel-api-responder.php`.

| Option | Default | Purpose |
| --- | --- | --- |
| `plural_data_key` | `entities` | Plain `response()` and collection responses |
| `singular_data_key` | `entity` | Singular REST actions |
| `using_for_rest` | `true` | Enable `forMethod()` / `fromAction()` REST naming |
| `methods_for_singular_key` | `['show', 'update']` | Singular action names |
| `without_wrapping` | `false` | Spread associative data into the root |

The package does **not** rewrite the incoming request's `Accept` header or install middleware. Laravel's `response()->json()` sets the outgoing JSON content type.

## 4.x upgrade notes

- Only PHP 8.5 and Laravel 13 are supported.
- Keep using `ResponseContract`, `BaseResponse` facade, the builder, custom keys and both PHP attributes.
- `deleted()` now correctly returns an empty HTTP 204 and does not accept unused data/message arguments.
- Unwrapped responses reject collisions involving `meta` and `message` instead of losing data silently.
- The obsolete `api-responder:init` command, global `paginate()` helper, vendor-overwriting stub and `force_json_response_header` config option were removed.
- `MetaResolver` no longer needs to be instantiated; use its static methods.
- Pagination supports three Laravel paginator types, and input data accepts `Arrayable`.

## Local development

CI is intentionally disabled. On PHP 8.5:

```bash
composer install
composer test
composer lint
```

Use `composer format` to apply Laravel Pint locally.
