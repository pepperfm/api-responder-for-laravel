# Changelog

## 4.1.0 — 2026-11-20 (breaking runtime requirements)

Publishes the maintained `4.x` implementation for PHP 8.5 and Laravel 13. Earlier `4.0.0` and `4.0.1` tags remain unchanged.

- Target PHP 8.5 and Laravel 13 exclusively.
- **Preserve** DI (`ResponseContract`), facade, published config, `entity/entities` naming, per-action REST names, `withDataKey()`, fluent `ResponseBuilder`, `#[ResponseDataKey]` and `#[WithoutWrapping]`.
- Fix direct `paginated($paginator)` type errors, including length-aware and cursor pagination; add `simplePaginate()` and Arrayable/Collection support.
- Reuse the native Laravel paginator metadata format instead of manually replicating paginator fields.
- Remove duplicated response-building code: the direct responder delegates to the fluent builder.
- Make `deleted()` return an empty 204 body; eliminate unused arguments.
- Reject reserved `meta` and `message` keys in unwrapped payloads instead of overwriting them; require `raw()` for root lists.
- Reject reserved response data keys.
- Fix attribute-based caller detection when invoked through facades.
- Remove nonfunctional `api-responder:init`, its unsafe vendor-overwriting stub, global `paginate()` helper and request-`Accept` rewriting middleware.
- Keep test routes confined to Testbench, not the package provider.
- Update local Pest/Testbench tooling, docs and coverage. No GitHub Actions workflow added.

### Migration

Applications must run Laravel 13 and PHP 8.5. Calls to `deleted($data, $message)` should become `deleted()`. Drop uses of `paginate()` global helper and `api-responder:init`. Use `raw()` for an unwrapped root list or payload containing envelope-reserved keys. Any custom global `force_json_response_header` setting is now ignored; handle content negotiation in the application.

## 3.0.2 — 2026-05-20

Added `raw()` responses, expanded the public response contract and removed unused Guzzle dependencies.
