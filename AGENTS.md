# API Responder for Laravel — project map

**PHP 8.5, Laravel 13 only.** Small configurable outgoing JSON responder. This 4.x series deliberately preserves the package's expressive API without supporting older runtime versions.

## Production code

- `src/ApiBaseResponder.php`: public concrete implementation, delegates success responses to builder and pagination to resolver.
- `src/ResponseBuilder.php`: **single JSON envelope implementation**; custom data keys, REST convention and optional PHP attributes.
- `src/MetaResolver.php`: extracts existing Laravel paginator metadata; supports length-aware, simple, cursor and mapped collections.
- `src/Contracts/ResponseContract.php`: DI contract.
- `src/Facades/BaseResponse.php`: Laravel facade.
- `src/Attributes/ResponseDataKey.php`, `WithoutWrapping.php`: opt-in per-action changes.
- `src/Providers/ApiBaseResponderServiceProvider.php`: registers DI binding and publishes the config.
- `config/config.php`: default naming and wrapping conventions.

No middleware, command, global helper, generated stub or CI workflows.

## Expected behavior

- Plain `response()`: configured plural key, no implicit stack inspection.
- `forMethod('show')` / `fromAction()`: optional singular REST convention.
- `withDataKey('users')` and `#[ResponseDataKey('users')]`: override the name.
- `withoutWrapping()`/attribute: spread only associative arrays without reserved keys, else throw; `raw()` returns exact payload.
- `stored()`: 201 with envelope; `deleted()`: 204 without body.
- `paginated()`: data plus `meta.pagination`, based on Laravel's native paginator `toArray()`.

## Tests and local checks

- `tests/TestCase.php` registers fixture routes; the production provider must not.
- Pest, Testbench and Pint; no GitHub Actions.

```bash
composer install
composer test
composer lint
composer format
```

Avoid new abstraction layers unless they solve a demonstrated problem. Do **not** delete user-facing configuration, REST naming, attributes or DI for line-count reduction.
