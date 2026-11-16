# API Responder

Read [AGENTS.md](AGENTS.md) for the 4.x code structure and rules.

PHP 8.5 / Laravel 13 only. Keep the original configurable response API: DI, facade, `entity/entities` keys, explicit REST naming, PHP attributes, and fluent builder.

`ApiBaseResponder` delegates success JSON construction to `ResponseBuilder`, and `MetaResolver` delegates pagination metadata to native Laravel. No global helpers, middleware, command/stub or GitHub CI workflow.

Local checks: `composer test`, `composer lint`.
