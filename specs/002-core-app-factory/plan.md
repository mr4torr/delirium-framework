# Implementation Plan: Core Application Factory

**Branch**: `002-core-app-factory` | **Date**: 2025-12-20 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/002-core-app-factory/spec.md`

## Summary

Implement the core bootstrap mechanism `DeliriumFactory` to initialize the application, along with a lightweight DI Container and an `#[AppModule]` attribute for modular architecture. This enables a "NestJS-style" setup where modules declare their dependencies and the framework wires them together. Critical requirement is recursive module scanning (e.g., `App -> Public -> Auth`) to build the complete Application Graph.

## Technical Context

**Language/Version**: PHP 8.4
**Primary Dependencies**: `openswoole/core`, `psr/container`, `psr/http-message`.
**Storage**: N/A
**Testing**: PHPUnit 12.5 (Unit and Integration tests).
**Target Platform**: Linux (OpenSwoole).
**Constraints**:
- Must support attribute-based meta-programming.
- Container must adhere to PSR-11.
- Factory must handle recursive module traversal (DFS).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Swoole-First**: ✅ `Application` wraps OpenSwoole Server.
- **Design Patterns**: ✅ Factory Method (`DeliriumFactory`), Singleton (`Application`), Module Pattern (`AppModule`), Composite (`Module` imports).
- **Stateless**: ✅ Container manages singletons; Request context isolated.
- **Strict Types**: ✅ Enforced.
- **Modular Architecture**: ✅ Core feature is the implementation of this principle.
- **Attribute-Driven**: ✅ `#[AppModule]` is the driver.

## Project Structure

### Documentation (this feature)

```text
specs/002-core-app-factory/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── ApplicationInterface.php
└── tasks.md
```


### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
# [REMOVE IF UNUSED] Option 1: Modular Application (NestJS-style)
src/
├── Main.php             # Entry point (creates App)
├── App.module.php       # Root Module
├── [Feature]/           # e.g., Users, Auth
│   ├── [Feature]Module.php
│   ├── [Feature]Controller.php
│   ├── [Feature]Service.php
│   ├── [Feature]Repository.php
│   └── [Feature]Dto.php
│   └── [Feature]Entity.php
└── Shared/
    ├── Infrastructure/
    └── Domain/

# [REMOVE IF UNUSED] Option 2: Modular Application
src/
├── Core/
│   ├── DeliriumFactory.php    # [NEW] Static Factory with Recursive Scanner
│   └── Application.php        # [NEW] Runtime Application Wrapper
├── Container/
│   └── Container.php          # [NEW] PSR-11 Container Implementation
├── Attribute/
│   └── AppModule.php          # [NEW] Module Definition Attribute
└── Contract/
    └── ApplicationInterface.php # [NEW] Contract
```

**Structure Decision**: Adopts the modular structure defined in the framework root `src/` (mapping `Delirium\` to `src/`).

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Internal Container | To support module pattern | External libs (php-di) add deps and might handle attributes differently; owning the container allows tighter integration with Module Graph. |
