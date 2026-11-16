---
name: api-responder-development
description: Use when building or modifying Laravel JSON API controllers with pepperfm/api-responder-for-laravel, including configurable entity/entities keys, ResponseContract DI, fluent response builders, PHP response attributes, errors, and pagination.
---

# API Responder for Laravel 4.x

Use the installed `pepperfm/api-responder-for-laravel` package to produce consistent outgoing JSON. This skill covers the **Laravel 13 / PHP 8.5** API. Prefer the installed package's actual methods if an application has a different release.

The package **formats responses**. It is not a replacement for Laravel Form Requests, API Resources, DTOs, or application authorization.

## Choose the smallest appropriate API

- **Ordinary endpoint:** inject `Pepperfm\ApiBaseResponder\Contracts\ResponseContract` and call `response()`, `stored()`, `deleted()`, or `error()`.
- **Explicit key:** use `withDataKey('users')->response($data)`.
- **REST method convention:** use `forMethod('show')->response($data)`.
- **Declarative per-action behavior:** use `#[ResponseDataKey(...)]` and/or `#[WithoutWrapping]` with `fromAction()`.
- **Pagination:** use `paginated($paginator)`; keep transformation and presentation responsibilities separate.
- **Exact JSON body without an envelope:** use `raw($data)`.

Do not add a custom response trait, wrapper, macro, service class, or DTO layer just to duplicate the package.

## Dependency injection and response naming

```php
use Illuminate\Http\JsonResponse;
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

final class UserController
{
    public function __construct(private ResponseContract $json) {}

    public function index(): JsonResponse
    {
        return $this->json->response(
            User::query()->get(['id', 'name'])->toArray(),
        );
    }

    public function show(User $user): JsonResponse
    {
        return $this->json->forMethod('show')->response([
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }
}
```

A direct `response()` uses the configured plural key (default: `entities`). An explicit `forMethod('show')` uses the singular key (default: `entity`), when REST naming is enabled. **Direct `response()` does not inspect the controller method automatically.**

Standard response format:

```json
{
  "entities": [{"id": 1, "name": "Anna"}],
  "meta": [],
  "message": "Success"
}
```

Only serialize fields intended for API clients. Prefer `JsonResource`, explicit field selection, or existing DTOs when controlling sensitive or nested fields; do not expose whole Eloquent models indiscriminately.

## Configuration and custom keys

Publish the package config only when customization is needed:

```bash
php artisan vendor:publish --provider="Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider" --tag="config"
```

`config/laravel-api-responder.php` controls:

- `plural_data_key` (default `entities`) and `singular_data_key` (default `entity`).
- `using_for_rest` (default `true`) and `methods_for_singular_key` (default `['show', 'update']`).
- `without_wrapping` (default `false`).

For a one-off key, prefer `$this->json->withDataKey('users')->response($data)` over changing global configuration. Do not use `meta` or `message` as data keys; those fields belong to the envelope.

## Attributes and fluent builder

```php
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;

#[ResponseDataKey('profile')]
public function show(User $user): JsonResponse
{
    return $this->json->fromAction()->response(['id' => $user->id]);
}

#[WithoutWrapping]
public function stats(): JsonResponse
{
    return $this->json->fromAction()->response(['active' => 3]);
}
```

Attributes take effect through `fromAction()`, **not** through plain `response()`. Use `fromAction(ControllerClass::class, 'method')` when automatic caller detection is unsuitable. The fluent equivalent for unwrapping is `$this->json->build()->withoutWrapping()->response($data)`.

Unwrapped data must be associative and must not define `meta` or `message`; otherwise use `raw()` to preserve the exact payload.

## Pagination

```php
// paginate(), simplePaginate(), and cursorPaginate() are supported:
$paginator = User::query()->paginate(20);

$paginator->through(fn (User $user) => [
    'id' => $user->id,
    'name' => $user->name,
]);

return $this->json->paginated($paginator);
```

For a separately mapped collection, keep the source paginator:

```php
$paginator = User::query()->paginate(20);
$items = $paginator->getCollection()->map(
    fn (User $user) => ['id' => $user->id],
);

return $this->json->paginated($items, $paginator);
```

The response includes data under the configured key and Laravel paginator metadata under `meta.pagination`. The pagination fields differ between length-aware, simple, and cursor paginators; do not assume `total` exists for every type.

## Status codes, errors, and raw responses

```php
return $this->json->stored(['id' => $user->id]);            // 201 Created
return $this->json->deleted();                              // 204 No Content, empty body
return $this->json->error('Not found', 404);                 // error JSON
return $this->json->error('Invalid', 422, ['email' => ['Required']]);
return $this->json->raw(['message' => 'Custom payload']);   // no envelope
```

- HTTP **204 must not carry a JSON body**. Use a 200 response if clients need a deletion message.
- Never use arbitrary exception codes as HTTP status codes without validation.
- An error response has `message` and `errors`; success responses use the configurable data key, `meta`, and `message`.
- Keep authorization, validation, and exception handling in their appropriate Laravel layers.

## Verification

Test the actual JSON body, status, configured key names, and the expected paginator type. In package development, run `composer test` and `composer lint` on PHP 8.5. Avoid introducing global helpers, request-`Accept` mutation, or additional runtime dependencies to solve response formatting alone.
