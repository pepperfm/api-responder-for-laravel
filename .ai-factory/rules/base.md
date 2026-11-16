# Rules

- PHP 8.5, Laravel 13 only.
- Production namespace: `Pepperfm\ApiBaseResponder`.
- Preserve the configurable envelope and opt-in PHP attribute behavior; avoid breaking the API for superficial simplicity.
- `ResponseBuilder` is the single JSON envelope formatter; `ApiBaseResponder` delegates rather than duplicating logic.
- `MetaResolver` uses native Laravel pagination metadata.
- Never write into package vendor files, register global helpers or rewrite incoming `Accept` headers.
- Test real behavior using Pest + Testbench, especially pagination, 204 responses, naming and facade-based attribute detection.
- Run formatting with Pint and tests locally; no CI workflow.
