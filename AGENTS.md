# AGENTS.md

> Project map for AI agents. Keep this file up-to-date as the project evolves.

## Project Overview
Laravel package providing standardized API JSON responses via DI/Facade with fluent ResponseBuilder, raw response support, PHP 8 attribute support, automatic REST-based data-key resolution, and pagination support.

## Tech Stack
- **Language:** PHP 8.5 only
- **Framework:** Laravel 13 only (library)
- **Testing:** Pest 5 + PHPUnit 13 + Testbench 11, local only
- **Linting:** PHP CS Fixer + Laravel Pint

## Project Structure
```
.
├── config/
│   └── config.php                  # Package configuration (data keys, wrapping, REST mode)
├── src/
│   ├── ApiBaseResponder.php        # Main responder — direct API + build()/forMethod()/withDataKey()/fromAction() bridge
│   ├── ResponseBuilder.php         # Fluent builder — explicit response construction with data key/wrapping control
│   ├── Contracts/
│   │   └── ResponseContract.php    # Public API interface bound in service container
│   ├── Attributes/
│   │   ├── ResponseDataKey.php     # #[ResponseDataKey('key')] — custom data key per method
│   │   └── WithoutWrapping.php     # #[WithoutWrapping] — disable data-key wrapping
│   ├── Facades/
│   │   └── BaseResponse.php        # Facade for static access (builder + direct methods)
│   ├── Providers/
│   │   └── ApiBaseResponderServiceProvider.php  # Auto-discovered service provider
│   ├── MetaResolver.php            # Resolves pagination meta — static resolve() + extractPagination()
├── tests/
│   ├── Fixtures/
│   │   ├── api.php                 # Test route definitions
│   │   ├── ExampleController.php   # Test controller fixture (forMethod/withDataKey usage)
│   │   └── AttributeController.php # Test controller fixture (fromAction + PHP attributes)
│   ├── Unit/
│   │   ├── ApiBaseResponderTest.php  # Direct API test suite (19 tests)
│   │   ├── ResponseBuilderTest.php   # ResponseBuilder tests (23 tests)
│   │   └── MetaResolverTest.php      # MetaResolver tests (6 tests)
│   ├── Pest.php                    # Pest configuration
│   └── TestCase.php                # Base test case (Orchestra Testbench)
├── composer.json                   # Package manifest
└── .ai-factory/
    ├── config.yaml                 # AI Factory language, paths, workflow, git, and rules configuration
    ├── DESCRIPTION.md              # Project specification and tech stack
    ├── ARCHITECTURE.md             # Architecture decisions and dependency rules
    ├── rules/
    │   └── base.md                 # Auto-detected project coding conventions
    └── plans/
        └── refactor-v3-clean-architecture.md  # Completed v3 refactoring plan
```

## Key Entry Points
| File | Purpose |
|------|---------|
| `src/Contracts/ResponseContract.php` | Public API — the interface consumers depend on |
| `src/ApiBaseResponder.php` | Core implementation — direct response methods + builder bridge |
| `src/ResponseBuilder.php` | Fluent builder — explicit data key, wrapping, and attribute support |
| `src/Providers/ApiBaseResponderServiceProvider.php` | Laravel auto-discovery, container bindings, config publishing |
| `config/config.php` | All configurable options (data keys, REST mode, wrapping) |
| `tests/Unit/ResponseBuilderTest.php` | Primary test suite for builder API |

## Documentation
| Document | Path | Description |
|----------|------|-------------|
| README | README.md | Package landing page with usage examples |
| Changelog | CHANGELOG.md | Version history |
| Contributing | CONTRIBUTING.md | Contribution guidelines |
| License | LICENSE.md | MIT license |
| External docs | [pepperfm.github.io](https://pepperfm.github.io/api-responder-for-laravel) | Full package documentation site |

## AI Context Files
| File | Purpose |
|------|---------|
| AGENTS.md | This file — project structure map |
| .ai-factory/DESCRIPTION.md | Project specification and tech stack |
| .ai-factory/ARCHITECTURE.md | Architecture decisions and guidelines |
| .ai-factory/config.yaml | AI Factory configuration for languages, paths, workflow, git, and rules |
| .ai-factory/rules/base.md | Auto-detected project conventions used by AI Factory workflows |

No GitHub Actions workflow is used. All tests and lint checks run locally.

The package does not mutate incoming Accept headers. `deleted()` returns a true empty HTTP 204. Use `raw()` for unwrapped root arrays or payloads with `meta` / `message` keys.

## Commands
| Command | Description |
|---------|-------------|
| `composer test` | Run Pest test suite |
| `composer lint` | Check code style (Pint + CS Fixer) |
| `composer lint-hard` | Auto-fix code style |
| `composer test-coverage` | Generate HTML coverage report |

## Agent Rules
- Never combine shell commands with `&&`, `||`, or `;` — execute each command as a separate Bash tool call. This applies even when a skill, plan, or instruction provides a combined command — always decompose it into individual calls.
  - Wrong: `git checkout 3.x && git pull`
  - Right: Two separate Bash tool calls — first `git checkout 3.x`, then `git pull origin 3.x`
