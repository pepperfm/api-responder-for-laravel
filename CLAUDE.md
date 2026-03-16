# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel Composer package (`pepperfm/api-responder-for-laravel`) that provides standardized API JSON responses via DI/Facade. Active branch: `3.x`.

## Commands

```bash
# Run tests
composer test

# Run a single test
vendor/bin/pest --filter="test name"

# Run a specific test file
vendor/bin/pest tests/Unit/ApiBaseResponderTest.php

# Check code style (dry-run)
composer lint

# Auto-fix code style
composer lint-hard
```

## Code Style

- `declare(strict_types=1)` is required in every PHP file (enforced by Pint)
- Laravel Pint preset with custom rules in `pint.json`
- Opening braces for classes/functions on **next line**, control structures on **same line**
- Closure fn spacing: none (`fn()` not `fn ()`)
- Import ordering: none (not alphabetical)
- `not_operator_with_successor_space`: false (use `!$var` not `! $var`)
- `phpdoc_to_property_type` and `phpdoc_to_return_type` enabled — prefer native types over PHPDoc

## Architecture

This is a **library package**, not a Laravel application. All classes live under `Pepperfm\ApiBaseResponder` namespace in `src/`.

### Core flow (v3)

**Direct API** — `ApiBaseResponder::response()`, `stored()`, `deleted()` use config-based key resolution via private `buildJsonResponse()`. No `debug_backtrace()`.

**Builder API** — `ApiBaseResponder` provides bridge methods that return `ResponseBuilder`:
- `build()` — create a blank fluent builder
- `forMethod('show')` — builder with REST method context
- `withDataKey('users')` — builder with explicit data key
- `fromAction()` — builder configured from PHP attributes on the calling method (uses opt-in minimal backtrace)

`ResponseBuilder` resolves the data key via:
1. Explicit `withDataKey()` — highest priority
2. PHP attributes (`#[ResponseDataKey]`) — read via `fromAction()`
3. REST convention (`forMethod()`) — `show`/`update` → singular, others → plural
4. Config fallback — `laravel-api-responder.plural_data_key`

`paginated()` delegates to `MetaResolver::resolve()` which extracts data and pagination meta from `LengthAwarePaginator`/`CursorPaginator`, then calls `response()`.

### Key contracts

- `ResponseContract` — the public interface consumers type-hint. Bound as singleton to `ApiBaseResponder` in the service container.
- `ResponseBuilder` — fluent builder returned by `build()`, `forMethod()`, `withDataKey()`, `fromAction()`. Not in the contract — it's a value object.
- `MetaResolver` — static `resolve()` for pagination meta extraction.

### Testing

Tests use **Orchestra Testbench** (not a real Laravel app). The `TestCase` base class registers `ApiBaseResponderServiceProvider`. Test routes are defined in `tests/Fixtures/api.php` and auto-loaded when `runningUnitTests()`. `ExampleController` and `AttributeController` in `tests/Fixtures/` serve as test fixture controllers.

Config key is `laravel-api-responder` (set via `mergeConfigFrom` in ServiceProvider).

## Context files

- `.ai-factory/DESCRIPTION.md` — project specification
- `.ai-factory/ARCHITECTURE.md` — architecture decisions and layer rules
- `AGENTS.md` — project structure map
