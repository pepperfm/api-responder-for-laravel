# Changelog

## 4.0.0 — Unreleased (breaking)

Simplify the package into a single responder for Laravel 13 and PHP 8.5.

- Replace `ApiBaseResponder`, contract, service provider, facade, builder, config, attributes and helpers with `Pepperfm\ApiResponder\JsonResponder`.
- Adopt one success envelope: `data`, `meta`, `message`. No automatic singular/plural keys.
- Expose four methods: `response()`, `created()`, `paginated()` and `error()`.
- Use Laravel's own `Paginator::toArray()` for length-aware, simple and cursor pagination; recommend `through()` for mapping.
- Delegate empty and unwrapped responses to native Laravel helpers.
- Remove redundant PHP-CS-Fixer configuration; use Laravel Pint for local code style.
- Require PHP 8.5 and Illuminate 13; retain local Pest / Testbench tests only.
- Refresh README, Writerside guides and agent documentation.

See README **Migration from 3.x to 4.x** before upgrading.

## Previous releases

3.x included configurable envelopes, fluent builders, attributes, facades and pagination utilities. Those customization surfaces were intentionally removed rather than maintained for backward compatibility.
