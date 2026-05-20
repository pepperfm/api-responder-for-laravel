# Project Base Rules

> Auto-detected conventions from the current Laravel package codebase. Edit as the project evolves.

## Naming Conventions

- PHP files: PascalCase for classes, matching the declared class name and PSR-4 namespace.
- Test files: PascalCase with `Test.php` suffix under `tests/Unit`.
- Variables and methods: camelCase.
- Config keys: snake_case in `config/config.php`.
- Classes and attributes: PascalCase; attributes live under `Pepperfm\ApiBaseResponder\Attributes`.

## Module Structure

- Public contracts live in `src/Contracts/` and define the consumer-facing API.
- Core response logic lives in `src/ApiBaseResponder.php`, `src/ResponseBuilder.php`, `src/MetaResolver.php`, and `src/helpers.php`.
- Laravel integration adapters live in `src/Providers/`, `src/Facades/`, `src/Http/Middleware/`, and `src/Console/`.
- Package configuration lives in `config/config.php` and is merged/published by the service provider.
- Tests live in `tests/Unit/`; HTTP/controller fixtures live in `tests/Fixtures/`.

## Error Handling

- API error responses are built through `ApiBaseResponder::error()` as JSON with `message` and `errors` keys.
- Pagination metadata resolution is explicit through `MetaResolver::resolve()` and `MetaResolver::extractPagination()`.
- Reflection/backtrace usage is limited to opt-in `fromAction()` attribute discovery; direct response methods should stay free of implicit caller inspection.

## Logging

- The package currently has no runtime logging calls.
- Keep logging out of core response construction unless a future feature explicitly introduces configurable diagnostics.

## Testing

- Use Pest tests under `tests/Unit`.
- Prefer focused assertions on the decoded JSON payload and HTTP status.
- Keep Orchestra Testbench fixtures under `tests/Fixtures`.
