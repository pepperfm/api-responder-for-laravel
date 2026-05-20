# Changelog

All notable changes to `laravel-api-responder` will be documented in this file

## 3.0.2 - 2026-05-20

### Added
- `raw(array $data, int $httpStatusCode = 200)` — return the payload directly without `entities`, `meta`, or `message` envelope fields

### Changed
- Expanded `ResponseContract` to declare the full public responder API directly
- Removed unused `guzzlehttp/guzzle` and `guzzlehttp/psr7` runtime dependencies

## 3.0.0 - 2026-XX-XX

### Breaking Changes
- Removed `Arrayable` type hints from all method signatures — use `->toArray()` before passing data
- Removed `FormatByWrappingOption` class — use `ResponseBuilder::withoutWrapping()` or config
- Removed `ValidateRestMethod` class — use `ResponseBuilder::forMethod()` or `withDataKey()`
- Removed `DataKeyStrategy` enum — data key resolution is now handled by `ResponseBuilder`
- Direct methods (`response()`, `stored()`, `deleted()`) no longer use `debug_backtrace()` — they resolve data key from config only
- Direct methods always use the plural data key from config (no implicit REST method detection)

### Added
- `ResponseBuilder` — fluent builder for explicit response construction
- `build()` method on `ApiBaseResponder` — creates a blank `ResponseBuilder`
- `forMethod(string $method)` — builder with REST method context (singular/plural key resolution)
- `withDataKey(string $key)` — builder with explicit data key
- `fromAction(?string $class, ?string $method)` — builder configured from PHP attributes (`#[ResponseDataKey]`, `#[WithoutWrapping]`), auto-detects caller when called without arguments
- `withoutWrapping()` on `ResponseBuilder` — disable data-key wrapping per-response
- `MetaResolver::resolve()` static method (replaces invokable pattern)
- `MetaResolver::extractPagination()` static method

### Removed
- `FormatByWrappingOption` class
- `ValidateRestMethod` class
- `DataKeyStrategy` enum
- All `debug_backtrace()` usage from core response methods
- All logging (`Log::debug`, `Log::warning`, `logger()`)
- `Arrayable` type support in method signatures

### Migration Guide (v2 -> v3)

**Before (v2):**
```php
// Implicit backtrace-based key detection
return $this->json->response($data);
```

**After (v3) — Option A: Config-based (simplest):**
```php
// Uses plural_data_key from config
return $this->json->response($data);
```

**After (v3) — Option B: Explicit builder:**
```php
// Explicit data key
return $this->json->withDataKey('users')->response($data);

// REST convention
return $this->json->forMethod('show')->response($data);
```

**After (v3) — Option C: PHP attributes:**
```php
#[ResponseDataKey('users')]
public function index(): JsonResponse {
    return $this->json->fromAction()->response($data);
}
```

## 1.0.0 - 201X-XX-XX

- initial release
