# API Responder for Laravel

One small class for consistent JSON responses in **Laravel 13 / PHP 8.5**.

The package formats HTTP responses. It does not validate requests, transform models, or replace Laravel API Resources.

## Installation

```bash
composer require pepperfm/api-responder-for-laravel
```

There is **no service provider, facade, config file or middleware**. Laravel's container automatically resolves the class.

## Usage

```php
use Illuminate\Http\JsonResponse;
use Pepperfm\ApiResponder\JsonResponder;

final class UserController
{
    public function __construct(private JsonResponder $json) {}

    public function index(): JsonResponse
    {
        return $this->json->paginated(
            User::query()->paginate(20)->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
            ]),
        );
    }

    public function show(User $user): JsonResponse
    {
        return $this->json->response(['id' => $user->id, 'name' => $user->name]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return $this->json->created(['id' => $user->id]);
    }

    public function destroy(User $user): \Symfony\Component\HttpFoundation\Response
    {
        $user->delete();

        return response()->noContent(); // Laravel handles 204 directly.
    }
}
```

Successful responses use one stable structure:

```json
{
  "data": [{"id": 1, "name": "Anna"}],
  "meta": [],
  "message": "Success"
}
```

Error responses:

```php
return $this->json->error('Not found', 404);
return $this->json->error('Validation failed', 422, [
    'email' => ['The email field is required.'],
]);
```

```json
{
  "message": "Validation failed",
  "errors": {"email": ["The email field is required."]}
}
```

## Four methods

| Method | Purpose |
| --- | --- |
| `response(mixed $data, array $meta = [], string $message = 'Success', int $status = 200)` | Standard success envelope |
| `created(mixed $data, array $meta = [])` | Same envelope, HTTP 201 |
| `paginated(LengthAwarePaginator|Paginator|CursorPaginator $paginator)` | Paginated data + `meta.pagination` |
| `error(string $message, int $status = 500, array $errors = [])` | Error JSON |

`paginated()` supports `paginate()`, `simplePaginate()` and `cursorPaginate()`. It uses **Laravel's own paginator serialization** and moves the `data` field to the top level, leaving all other paginator keys under `meta.pagination`. No custom pagination parser.

To transform paginated records, use Laravel's existing `through()` method before passing the paginator.

For responses without an envelope, use Laravel's `response()->json($payload)` directly. For HTTP 204, use `response()->noContent()`. A second API for either would only duplicate framework behavior.

### Field visibility

Laravel can JSON-encode Eloquent models and Collections, but that does **not** make every field safe to expose. Use `JsonResource`, DTOs, or explicit field selection where you need a public API contract.

## Migration from 3.x to 4.x

**4.x is intentionally not backward compatible.** It requires PHP 8.5 and Laravel 13.

- Use `Pepperfm\ApiResponder\JsonResponder` with ordinary constructor injection instead of `ResponseContract`, `ApiBaseResponder`, the old facade or the package provider.
- `response()` always writes data under **`data`**, not `entities` or `entity`. Update consumers accordingly.
- `stored()` becomes `created()`. For delete, use Laravel `response()->noContent()`; for raw JSON use `response()->json()`.
- `paginated()` takes **one paginator**. Map its items using `->through()` rather than passing a separate mapped array or collection.
- The builder, `withDataKey()`, `forMethod()`, `fromAction()`, PHP attributes, wrapping configuration, global helpers and automatic middleware are gone. Custom endpoint-specific envelopes belong in application code.
- There are no configuration files to publish. Delete obsolete published `config/laravel-api-responder.php` if you no longer use it.

This is a wire-format change: update frontend typings and API consumers before upgrading.

## Local checks

No GitHub Actions workflows are provided.

```bash
composer install
composer test
composer lint
```

Run on PHP 8.5 with the project dependencies installed.
