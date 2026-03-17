# Api responder for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pepperfm/api-responder-for-laravel.svg?style=flat-square)](https://packagist.org/packages/pepperfm/api-responder-for-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/pepperfm/api-responder-for-laravel.svg?style=flat-square)](https://packagist.org/packages/pepperfm/api-responder-for-laravel)
![GitHub Actions](https://github.com/pepperfm/api-responder-for-laravel/actions/workflows/main.yml/badge.svg)

![logo](api_responder.jpg)

## Standardized API JSON responses for Laravel

> [!TIP]
> <a href="https://pepperfm.github.io/api-responder-for-laravel" target="_blank">Full Package Description</a>: Helpful advice for doing things better or more easily.

## Installation

```bash
composer require pepperfm/api-responder-for-laravel
```

## Usage

### Inject via Laravel DI

```php
use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;

class UserController extends Controller
{
    public function __construct(public ResponseContract $json)
    {
    }
}
```

### Direct API (config-based data key)

The simplest approach — data key and wrapping are resolved from config:

```php
public function index(Request $request)
{
    $users = User::query()->whereIn('id', $request->input('ids'))->get();

    return $this->json->response($users->toArray());
}

public function store(StoreUserRequest $request)
{
    $user = User::create($request->validated());

    return $this->json->stored($user->toArray());
}

public function destroy(User $user)
{
    $user->delete();

    return $this->json->deleted();
}
```

### Builder API (explicit control)

Use the fluent builder when you need per-response control over data keys or wrapping:

```php
// Explicit data key
public function index()
{
    $users = User::all();

    return $this->json->withDataKey('users')->response($users->toArray());
}

// REST method convention (singular key for show/update, plural for index/etc.)
public function show(User $user)
{
    return $this->json->forMethod('show')->response($user->toArray());
}

// Disable wrapping (data spread into response root)
public function stats()
{
    return $this->json->build()->withoutWrapping()->response($stats);
}
```

### PHP Attributes (declarative style)

Use `#[ResponseDataKey]` and `#[WithoutWrapping]` attributes with `fromAction()`:

```php
use Pepperfm\ApiBaseResponder\Attributes\ResponseDataKey;
use Pepperfm\ApiBaseResponder\Attributes\WithoutWrapping;

#[ResponseDataKey('user')]
public function show(User $user): JsonResponse
{
    return $this->json->fromAction()->response($user->toArray());
}

#[ResponseDataKey] // defaults to singular 'entity'
public function edit(User $user): JsonResponse
{
    return $this->json->fromAction()->response($user->toArray());
}

#[WithoutWrapping]
public function stats(): JsonResponse
{
    return $this->json->fromAction()->response($stats);
}
```

### Pagination

```php
public function index(Request $request)
{
    $users = User::query()->paginate();

    return $this->json->paginated($users);
}

// With data mapping
public function index(Request $request)
{
    $users = User::query()->paginate();
    $dtoCollection = $users->getCollection()->mapInto(UserDto::class);

    return $this->json->paginated($dtoCollection->toArray(), $users);
}
```

### Error responses

```php
return $this->json->error('Not found', 404);
return $this->json->error('Validation failed', 422, $validator->errors());
```

### Facades

```php
use Pepperfm\ApiBaseResponder\Facades\BaseResponse;

return BaseResponse::response($data);
return BaseResponse::forMethod('index')->response($data);
return BaseResponse::fromAction()->response($data);
```

### Method injection / resolve

```php
public function index(Request $request, ResponseContract $json)
{
    return $json->response($users->toArray());
}

// or
return resolve(ResponseContract::class)->response($users->toArray());
```

## Paginated response format

The pagination meta is extracted automatically from `LengthAwarePaginator` or `CursorPaginator`:

```ts
export interface IPaginatedResponse<T> {
    current_page: number
    per_page: number
    last_page: number
    data: T[]
    from: number
    to: number
    total: number
    prev_page_url?: any
    next_page_url: string
    links: IPaginatedResponseLinks[]
}

export interface IPaginatedResponseLinks {
    url?: any
    label: string
    active: boolean
}
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="Pepperfm\ApiBaseResponder\Providers\ApiBaseResponderServiceProvider" --tag="config"
```

Key options:
- `plural_data_key` — data key for collections (default: `'entities'`)
- `singular_data_key` — data key for single items (default: `'entity'`)
- `without_wrapping` — disable data-key wrapping globally (default: `false`)
- `using_for_rest` — enable REST method-based key resolution (default: `true`)
- `methods_for_singular_key` — methods that use singular key (default: `['show', 'update']`)
- `force_json_response_header` — auto-add `Accept: application/json` to API requests (default: `true`)

### Check the configuration file to customize response wrapping as you prefer

---

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email Damon3453@yandex.ru instead of using the issue tracker.

## Credits

-   [Dmitry Gaponenko](https://github.com/pepperfm)
-   [Website](https://pepperfm.ru)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
