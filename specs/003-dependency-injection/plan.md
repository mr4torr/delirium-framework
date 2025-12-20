# Implementation Plan: Dependency Injection

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Implement a PSR-11 compliant Dependency Injection system as a **dedicated package** (`delirium/dependency-injection`, namespace `Delirium\DI`). This package will rely on `symfony/dependency-injection`. The `core` package will consume this new package. The solution will support:
1.  **Attribute-based Injection**: `#[Inject]` for properties (including private).
2.  **Implicit Registration**: Automatic discovery of type-hinted dependencies in Controllers (Constructors and **Functions**/Methods) without explicit `providers` config.
3.  **Performance**: Compilation of the container to a PHP file (`var/cache/dependency-injection.php`) for zero-reflection runtime.

## Technical Context

**Language/Version**: PHP 8.4+
**Primary Dependencies**: `symfony/dependency-injection`, `symfony/config`
**Storage**: File system for cached container (`var/cache/`)
**Testing**: PHPUnit (Integration tests inside Swoole context)
**Target Platform**: Linux / OpenSwoole
**Project Type**: Framework Core Package
**Performance Goals**: Zero-reflection at runtime (boot time excluded).
**Constraints**: Must work within OpenSwoole long-running process model.
**Scale/Scope**: Core functionality affecting all application modules.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **I. Swoole-First**: Caching mechanism allows building container once at boot, avoiding runtime reflection cost per request.
- [x] **II. Design Patterns**: Uses **Creational Patterns**, **Module Pattern**, and **Compiler Pass**.
- [x] **III. Stateless**: Container is immutable after boot (compiled).
- [x] **VI. Attribute-Driven**: Uses `#[Inject]` and `#[Module]`.

## Project Structure

### Documentation (this feature)

```text
specs/003-dependency-injection/
├── plan.md              # This file
├── research.md          # Implementation strategy
├── data-model.md        # DI Container Entities
├── quickstart.md        # Usage guide
├── contracts/           # No new public contracts
└── checklists/          # Quality checks
```

### Source Code (repository root)

```text
packages/dependency-injection/src/ # [NEW] Dedicated Package (Namespace: Delirium\DI)
├── ContainerBuilder.php            # Wrapper for Symfony Builder & Compiler
├── Attribute/
│   └── Inject.php                  # Attribute definition
└── Compiler/
    └── PropertyInjectionPass.php   # Handle #[Inject] & Private properties
    └── DiscoveryPass.php           # [NEW] Handle Implicit Registration (Constructors & Methods)

packages/core/src/
├── AppFactory.php                  # [update] Uses Delirium\DI\ContainerBuilder
└── Container/
    └── Container.php               # [remove/refactor] usage of old container
```

**Structure Decision**: dedicated `delirium/dependency-injection` package, namespace `Delirium\DI`.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Additional Package | Separation of concerns. | User requested explicit separate package. |
| Additional Dependency | Robust DI is complex. | `php-di` or custom parsing is harder to optimize/cache well for Swoole. |
