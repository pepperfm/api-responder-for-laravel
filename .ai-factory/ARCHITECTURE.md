# Architecture: Layered Architecture (Library Package)

## Overview
This project is a Laravel Composer package (library), not an application. It follows a Layered Architecture adapted for the package context: a clear separation between the public contract layer, the core logic layer, the configuration/integration layer, and the framework adapter layer (ServiceProvider, Middleware, Facade).

This architecture was chosen because the package is small, focused on a single responsibility (API response formatting), and consumed as a dependency by Laravel applications. Complex patterns like DDD or Clean Architecture would be over-engineering for a library of this scope.

## Decision Rationale
- **Project type:** Composer library package (not a standalone application)
- **Tech stack:** PHP 8.2+, Laravel >= 10.20
- **Key factor:** Single-responsibility library with a small API surface — simplicity and clarity are paramount

## Folder Structure
```
src/
├── Contracts/                     # Layer 1: Public API (contracts consumers depend on)
│   └── ResponseContract.php       # Interface defining response(), error()
├── Attributes/                    # Layer 2: Metadata / Declarative configuration
│   ├── ResponseDataKey.php        # #[ResponseDataKey] — custom data key
│   └── WithoutWrapping.php        # #[WithoutWrapping] — disable wrapping
├── ApiBaseResponder.php           # Layer 3: Core Logic — implements ResponseContract, bridges to ResponseBuilder
├── ResponseBuilder.php            # Layer 3: Core Logic — fluent builder for explicit response construction
├── MetaResolver.php               # Layer 3: Core Logic — pagination meta extraction
├── helpers.php                    # Layer 3: Core Logic — paginate() global helper
├── Facades/                       # Layer 4: Framework Adapter — Laravel Facade
│   └── BaseResponse.php
├── Http/
│   └── Middleware/                 # Layer 4: Framework Adapter — HTTP Middleware
│       └── ForceJsonResponse.php
├── Console/                       # Layer 4: Framework Adapter — Artisan commands
│   └── InitCommand.php
└── Providers/                     # Layer 4: Framework Adapter — Service Provider
    └── ApiBaseResponderServiceProvider.php

config/
└── config.php                     # Configuration — published to consuming app
```

## Dependency Rules

- Layer 1 (Contracts) depends on nothing except Laravel HTTP contracts (`JsonResponse`)
- Layer 2 (Attributes) depends on nothing (pure PHP attributes)
- Layer 3 (Core Logic) depends on Layer 1 (Contracts) + Layer 2 (Attributes) + Laravel Pagination contracts
- Layer 4 (Framework Adapter) depends on Layer 1 (Contracts) + Layer 3 (Core Logic) + Laravel framework

Direction of dependencies:
- Framework Adapter -> Core Logic -> Contracts
- Framework Adapter -> Core Logic -> Attributes

Forbidden:
- Contracts MUST NOT depend on Core Logic or Framework Adapter
- Core Logic MUST NOT depend on Framework Adapter (ServiceProvider, Middleware, Facade)
- Attributes MUST NOT depend on anything in this package

## Layer/Module Communication
- **Consumer -> Package:** via `ResponseContract` (DI) or `BaseResponse` Facade
- **Core Logic internals:** `ApiBaseResponder` provides direct config-based responses and delegates to `ResponseBuilder` for fine-grained control. `MetaResolver` handles pagination meta extraction.
- **Reflection bridge (opt-in):** `fromAction()` uses `debug_backtrace()` + `ReflectionMethod` to read PHP attributes from the calling controller method — this is opt-in and only used when the consumer explicitly calls `fromAction()`

## Key Principles
1. **Contract-first:** All public API is defined in `ResponseContract`. Consumers type-hint the interface, never the concrete class.
2. **Dual API surface:** Direct methods (`response()`, `error()`, `stored()`, `deleted()`) use config-based key resolution. Builder methods (`build()`, `forMethod()`, `withDataKey()`, `fromAction()`) return a fluent `ResponseBuilder` for explicit control.
3. **Raw responses are explicit:** `raw()` bypasses envelope fields intentionally; standard response methods keep the package envelope contract.
4. **No implicit magic:** Core response methods (`response()`, `stored()`, `deleted()`) never use `debug_backtrace()`. The only backtrace usage is in the opt-in `fromAction()` for auto-detecting the caller.
5. **Configuration over convention override:** Default REST behavior (entity/entities) works out-of-the-box; PHP attributes (`#[ResponseDataKey]`, `#[WithoutWrapping]`) and config options allow overrides without subclassing.
6. **Minimal dependencies:** The package has no runtime Composer dependencies beyond PHP itself. Laravel framework compatibility is enforced through the `laravel/framework` conflict rule and the package is intended to run inside a compatible Laravel host application.

## Code Examples

### Adding a new response method
When adding a new response method (e.g., `updated()`), follow the existing pattern:

```php
// 1. Add method signature to ResponseContract (Layer 1) or @method PHPDoc
// 2. Implement in ApiBaseResponder (Layer 3)
public function updated(
    array $data = [],
    array $meta = [],
    string $message = 'Updated',
): JsonResponse {
    return $this->buildJsonResponse($data, $meta, $message, JsonResponse::HTTP_OK);
}

// 3. Add matching method to ResponseBuilder for builder API
public function updated(
    array $data = [],
    array $meta = [],
    string $message = 'Updated',
): JsonResponse {
    return $this->buildJsonResponse($data, $meta, $message, JsonResponse::HTTP_OK);
}
```

### Using the builder API
```php
// Explicit data key
$this->json->withDataKey('users')->response($data);

// REST convention
$this->json->forMethod('show')->response($data);

// PHP attributes (auto-detected from caller)
#[ResponseDataKey('my_items')]
public function index(): JsonResponse {
    return $this->json->fromAction()->response($data);
}

// Without wrapping
$this->json->build()->withoutWrapping()->response($data);
```

### Adding a new PHP attribute
```php
// src/Attributes/CustomAttribute.php (Layer 2 — no dependencies)
#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class CustomAttribute
{
    public function __construct(private string $value = 'default') {}

    public function value(): string
    {
        return $this->value;
    }
}
```

### Writing a test (Pest)
```php
test('response with explicit data key', function () {
    $builder = ResponseBuilder::make()->withDataKey('users');
    $response = $builder->response(['name' => 'John']);
    $data = $response->getData(true);

    expect($data)
        ->toHaveKeys(['users', 'meta', 'message'])
        ->and($data['users'])->toBe(['name' => 'John']);
});
```

## Anti-Patterns
- Never import `ApiBaseResponderServiceProvider`, `BaseResponse` Facade, or `ForceJsonResponse` middleware inside Core Logic classes — framework adapters are outer layer
- Never add database dependencies or Eloquent models — this package is a response formatter, not a data layer
- Never hardcode data keys — always read from config or attributes
- Never use `debug_backtrace()` in core response methods — it's only acceptable in the opt-in `fromAction()`
- Avoid adding heavy dependencies — the package's value is in being lightweight and focused
