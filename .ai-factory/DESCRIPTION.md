# API Responder for Laravel 4.x

A minimal Composer library for consistent outgoing HTTP JSON response envelopes.

Requirements: PHP 8.5 / Laravel 13.

Runtime source: `src/JsonResponder.php`; namespace `Pepperfm\ApiResponder`.

Public methods: `response`, `created`, `paginated`, `error`.

Default success output is `{"data": ..., "meta": [], "message": "Success"}`.
Pagination uses Laravel's paginator serialization, with metadata under `meta.pagination`.

No DTOs, validation, service provider, contracts, facades, builder, custom configuration or automatic request mutation. Laravel handles `response()->json()` and `response()->noContent()`.

Run Pest tests and Pint locally. No CI workflow is included.
