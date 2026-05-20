# Project: Api Responder for Laravel

## Overview
A Laravel package (library) that provides a standardized, configurable API JSON response layer via Dependency Injection, Facade, or Service Container resolution. It offers a fluent ResponseBuilder API for explicit data key control and supports PHP 8 attributes for declarative configuration, pagination (LengthAware + Cursor), and flexible wrapping control.

## Core Features
- Standardized JSON responses for success, error, create (stored), and delete operations
- **Fluent ResponseBuilder API:** `$this->json->forMethod('index')->response($data)` or `$this->json->withDataKey('users')->response($data)` — explicit, testable, no backtrace magic
- **PHP Attribute support (opt-in):** `#[ResponseDataKey('key')]` and `#[WithoutWrapping]` via `fromAction()` — uses minimal backtrace only when explicitly called
- Automatic data-key resolution: `entities` (plural) vs `entity` (singular) based on REST method names (`show`, `update`)
- Response wrapping control via `#[WithoutWrapping]` attribute, config, or `->withoutWrapping()` builder method
- Pagination support: `LengthAwarePaginator` and `CursorPaginator` with structured meta via `MetaResolver::resolve()`
- `ForceJsonResponse` middleware for ensuring `Accept: application/json`
- Facade (`BaseResponse` / `\ApiBaseResponder`) and DI (`ResponseContract`) access patterns

## Tech Stack
- **Language:** PHP 8.2+
- **Framework:** Laravel >= 10.20 (as a library/package — not an application)
- **Testing:** Pest 3 + PHPUnit 11
- **Linting:** PHP CS Fixer + Laravel Pint
- **Runtime HTTP dependencies:** Guzzle 7 + PSR-7
- **Dev Tools:** Orchestra Testbench 9, Spatie Ray

## Architecture Notes
- **Package type:** Composer library with Laravel auto-discovery (ServiceProvider + Facade alias)
- **Contract-driven:** `ResponseContract` interface defines the public API; `ApiBaseResponder` is the concrete implementation bound in the service container
- **Dual API:** Direct methods use config-based key resolution; Builder methods (`build()`, `forMethod()`, `withDataKey()`, `fromAction()`) return fluent `ResponseBuilder` for explicit control
- **No implicit backtrace:** Core response methods never use `debug_backtrace()`. Only the opt-in `fromAction()` uses minimal backtrace (1 frame, no args) to auto-detect the calling method
- **Configuration:** Published config `laravel-api-responder` controls data keys, REST mode, wrapping, and force-JSON header
- **Branch strategy:** `3.x` is the active development branch; `master` is the stable release branch

## Architecture
See `.ai-factory/ARCHITECTURE.md` for detailed architecture guidelines.
Pattern: Layered Architecture (Library Package)

## Non-Functional Requirements
- UTF-8 enforced in all JSON responses (`JSON_UNESCAPED_UNICODE`)
- Configurable response headers (`Content-Type: application/json; charset=UTF-8`)
- Minimal dependencies: Guzzle HTTP + PSR-7 as runtime requirements beyond PHP itself
- Compatible with Laravel 10.20+ (conflict rule prevents older versions)
