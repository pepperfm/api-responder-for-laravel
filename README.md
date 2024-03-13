# Api responder for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pepperfm/api-responder-for-laravel.svg?style=flat-square)](https://packagist.org/packages/pepperfm/api-responder--for-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/pepperfm/api-responder-for-laravel.svg?style=flat-square)](https://packagist.org/packages/pepperfm/api-responder-for-laravel)
![GitHub Actions](https://github.com/pepperfm/api-responder-for-laravel/actions/workflows/main.yml/badge.svg)

Easy api responder template using via DI

## Installation

You can install the package via composer:

```bash
composer require pepperfm/api-responder-for-laravel
```

## Usage
### Simply using by laravel DI features.

### In **use** section:

`use Pepperfm\ApiBaseResponder\Contracts\ResponseContract;`

Then any options you like:

```php

public function __construct(public ResponseContract $json)
{
}

public function index(Request $request, ResponseContract $json)
{
    $users = User::whereIn('id', $request->input('ids'))->get();

    return $this->json->response($users, meta($users));
}
```

or
```php
public function index(Request $request, ResponseContract $json)
{
    return $this->json->response($users, meta($users));
}
```
or
```php
public function index(Request $request)
{
    return app(ResponseContract::class)->response($users, meta($users));
}
```
### Or would you prefer facades?
```php
public function index(Request $request)
{
    return \ApiBaseResponder::response($users, meta($users));
}
```
or
```php
use Pepperfm\ApiBaseResponder\Facades\BaseResponse;

public function index(Request $request)
{
    return BaseResponse::response($users, meta($users));
}
```

[//]: # (## Console)

[//]: # (If you want to add `OAuthError&#40;&#41;` method, please, run)

[//]: # (```bash)

[//]: # (php artisan api-responder:init)

[//]: # (```)

[//]: # (command.)

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
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
