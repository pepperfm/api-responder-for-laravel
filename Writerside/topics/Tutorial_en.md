# API Responder 4.x — Laravel 13 / PHP 8.5

A tiny outgoing JSON formatter: one class, four methods, no trait, service provider, facade, config, DTO or attributes.

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

        return response()->noContent(); // native Laravel
    }
}
```

Success JSON has one predictable shape: `{"data": ..., "meta": [], "message": "Success"}`. For errors call `$json->error('Not found', 404)`.

The same `paginated()` method supports `paginate()`, `simplePaginate()` and `cursorPaginate()`. Map records beforehand with `$paginator->through(fn ($model) => ...)`.

Use Laravel `response()->json()` for raw payloads. Version 4 removes the former builders, attribute discovery, dynamic data keys, facade, interface, helper functions and `stored()` / `deleted()` methods.

See the [README](https://github.com/pepperfm/api-responder-for-laravel#readme) for full usage and the migration guide.
