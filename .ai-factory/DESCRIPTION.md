# Project: Api Responder for Laravel

## Overview
A Laravel package (library) that provides a standardized, configurable API JSON response layer via Dependency Injection, Facade, or Service Container resolution. It offers a fluent ResponseBuilder API for explicit data key control and supports PHP 8 attributes for declarative configuration, pagination (LengthAware + Simple + Cursor), and flexible wrapping control.

## Core Features
- Standardized JSON responses for success, error, create (stored), and delete operations
- Raw JSON responses for endpoints that should return the payload directly without an envelope
- **Fluent ResponseBuilder API:** `$this->json->forMethod('index')->response($data)` or `$this->json->withDataKey('users')->response($data)` — explicit, testable, no backtrace magic
- **PHP Attribute support (opt-in):** `#[ResponseDataKey('key')]` and `#[WithoutWrapping]` via `fromAction()` — uses minimal backtrace only when explicitly called
- Automatic data-key resolution: `entities` (plural) vs `entity` (singular) based on REST method names (`show`, `update`)
- Response wrapping control via `#[WithoutWrapping]` attribute, config, or `->withoutWrapping()` builder method
- Pagination support: `LengthAwarePaginator` and `CursorPaginator` with structured meta via `MetaResolver::resolve()`
- Facade (`BaseResponse` / `\ApiBaseResponder`) and DI (`ResponseContract`) access patterns

## Tech Stack
- **Language:** PHP 8.5
- **Framework:** Laravel 13 (as a library/package — not an application)
- **Testing:** Pest 5 + PHPUnit 13
- **Linting:** PHP CS Fixer + Laravel Pint
- **Runtime dependencies:** PHP 8.5 and a Laravel 13 host application
- **Dev Tools:** Orchestra Testbench 11

## Architecture Notes
- **Package type:** Composer library with Laravel auto-discovery (ServiceProvider + Facade alias)
- **Contract-driven:** `ResponseContract` interface defines the public API; `ApiBaseResponder` is the concrete implementation bound in the service container
- **Dual API:** Direct methods use config-based key resolution; Builder methods (`build()`, `forMethod()`, `withDataKey()`, `fromAction()`) return fluent `ResponseBuilder` for explicit control
- **No implicit backtrace:** Core response methods never use `debug_backtrace()`. Only the opt-in `fromAction()` uses minimal backtrace (1 frame, no args) to auto-detect the calling method
- **Branch strategy:** `3.x` is the active development branch; `master` is the stable release branch

## Architecture
See `.ai-factory/ARCHITECTURE.md` for detailed architecture guidelines.
Pattern: Layered Architecture (Library Package)

## Non-Functional Requirements
- UTF-8 enforced in all JSON responses (`JSON_UNESCAPED_UNICODE`)
- Configurable response headers (`Content-Type: application/json; charset=UTF-8`)
- Minimal dependencies: no runtime Composer dependencies beyond PHP itself; the package is intended to run inside a compatible Laravel host application
- Compatible with Laravel 13 (conflict rule prevents older versions)
