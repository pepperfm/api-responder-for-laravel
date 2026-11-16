# Architecture

This is a deliberately minimal **Laravel 13 / PHP 8.5** JSON response formatter.

The only runtime source is `src/JsonResponder.php` under `Pepperfm\ApiResponder`.

- No service provider, facade, contract, builder, attributes, configuration or middleware.
- Dependency injection uses Laravel's automatic concrete class resolution.
- Success JSON: `data`, `meta`, `message`. Error JSON: `message`, `errors`.
- Pagination is delegated to Laravel's `toArray()` and `through()`.
- Do not replicate raw JSON or 204 No Content helpers; Laravel already implements these.

No support for API versions 1–3 or any framework version before Laravel 13 is required.
