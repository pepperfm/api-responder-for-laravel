# Architecture — API Responder 4.x

**PHP 8.5 / Laravel 13.** Configurable outgoing JSON response formatting, not DTO validation or serialization.

## Layers

- `ResponseContract`: injectable public interface.
- `ApiBaseResponder`: DI implementation; delegates success responses to `ResponseBuilder`.
- `ResponseBuilder`: single envelope formatter, with configurable keys and opt-in reflection for PHP attributes.
- `MetaResolver`: paginator metadata extraction using Laravel's native `toArray()`.
- `ApiBaseResponderServiceProvider`, `BaseResponse`: Laravel integration and optional facade.
- `config/config.php`: central naming and wrapping settings.

The public surface supports standard responses, errors, creation, deletion, raw output, three paginator flavors, REST method naming, explicit keys and PHP attributes.

There is no automatic request mutation, global helper, package-specific CLI command or CI workflow. Prefer simplifying internals while preserving the features users selected this package for.
