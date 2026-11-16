# Project rules

- PHP 8.5 and Laravel 13 only.
- Namespace: `Pepperfm\ApiResponder`; PSR-4 matches `src/`.
- Keep production code in `src/JsonResponder.php`. Avoid new layers or configuration without a concrete need.
- Do not rewrite request headers or register global functions.
- Prefer native Laravel helpers, paginator methods and JSON serialization.
- Test response shapes and HTTP statuses with Pest / Testbench under `tests/Unit/`.
- Format using Laravel Pint. No GitHub Actions workflow.
