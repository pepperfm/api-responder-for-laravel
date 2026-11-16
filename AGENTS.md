# API Responder — agent guidance

A small Composer package targeting **PHP 8.5 and Laravel 13**.

## Architecture

- `src/JsonResponder.php`: the **only production PHP class**.
- Namespace: `Pepperfm\ApiResponder`.
- It formats outgoing JSON only; it does not provide DTOs, validation, transformations, routes, commands, middleware, service providers or configuration.
- Public methods: `response()`, `created()`, `paginated()`, `error()`.
- Use one response shape (`data`, `meta`, `message`). Errors use `message` and `errors`.
- For raw JSON use native Laravel `response()->json()`; for HTTP 204 use `response()->noContent()`.
- `paginated()` relies on the paginator's `toArray()` and `through()`; do not reimplement Laravel metadata.
- Laravel's container can instantiate `JsonResponder` without a service provider.

## Local verification

No GitHub Actions workflows. Run on PHP 8.5:

```bash
composer install
composer test
composer lint
```

Tests: `tests/Unit/JsonResponderTest.php` with Pest and Orchestra Testbench.
Formatting: Laravel Pint. Keep this package narrowly scoped and do not reintroduce 3.x compatibility layers.
