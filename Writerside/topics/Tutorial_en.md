# Standardizing Laravel JSON Responses Without Traits

API Responder formats **outgoing HTTP responses**. It is intentionally not a DTO, transformation, or validation library.

**Requirements:** PHP 8.5 and Laravel 13.

## Installation

```bash
composer require pepperfm/api-responder-for-laravel
```

## Example

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

The `index()` response has no extra `response.data` wrapper:

```json
{
  "entities": [{"id": 1, "name": "Anna"}],
  "meta": [],
  "message": "Success"
}
```

`deleted()` returns **204 No Content** with an empty body. Use an explicit 200 response when the client expects a JSON message.

## Pagination

```php
return $this->json->paginated(User::query()->paginate());
return $this->json->paginated(User::query()->simplePaginate());
return $this->json->paginated(User::query()->cursorPaginate());

// Map data while retaining the original pagination metadata:
$paginator = User::query()->paginate();
$data = $paginator->getCollection()->map(
    fn ($user) => ['id' => $user->id, 'name' => $user->name],
);

return $this->json->paginated($data, $paginator);
```

Pagination details live under `meta.pagination`. Available fields depend on the paginator type.

## Other response methods

```php
return $this->json->stored(['id' => 42]); // 201
return $this->json->error('Not found', 404);
return $this->json->raw(['message' => 'Custom']); // exact JSON payload
return $this->json->forMethod('show')->response(['id' => 1]);
```

Payloads may be arrays or Laravel `Arrayable` objects, including `Collection`. Prefer API Resources or explicit DTOs for field visibility.

## Attributes and wrapping

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

Unwrapped responses reserve the `meta` and `message` keys. Rather than silently overwriting those fields, the builder throws an exception. Use `raw()` for root lists or arbitrary JSON.

The package does **not** mutate incoming `Accept` headers or automatically register middleware. Tests are local (`composer test`, `composer lint`).

See the [README](https://github.com/pepperfm/api-responder-for-laravel/blob/3.x/README.md) for the full API.
