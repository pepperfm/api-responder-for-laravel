# API Responder for Laravel 4.x

A lightweight configurable response formatter for **PHP 8.5 / Laravel 13**.

- Dependency injection via `ResponseContract` and optional facade.
- Standard `response()`, `raw()`, `stored()`, `deleted()`, `error()`, `paginated()` methods.
- Published configuration controls plural/singular key names, REST naming and wrapping.
- Fluent builder controls per-response key, REST context and wrapping.
- PHP attributes support `#[ResponseDataKey]` and `#[WithoutWrapping]` through explicit `fromAction()`.
- Laravel `Arrayable`, Collection, LengthAwarePaginator, simple Paginator and CursorPaginator support.
- Direct responder delegates formatting to the builder. Metadata uses paginator `toArray()`.
- No runtime middleware, global functions or console commands. Testbench tests run locally without CI.

This package formats outgoing JSON, not DTOs or validation.
