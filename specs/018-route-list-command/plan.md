# Implementation Plan: Route List Command

**Branch**: `018-route-list-command` | **Date**: 2026-02-14 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/018-route-list-command/spec.md`

## Summary

Implement a `route:list` console command to display all registered routes in the application. This involves creating a `HttpRouterServiceProvider` in the `http-router` package to register the command, and the command itself which will retrieve routes from `RouteRegistry` and display them in a table.

## Technical Context

**Language/Version**: PHP 8.4
**Primary Dependencies**: `symfony/console`, `delirium/http-router`
**Storage**: N/A (Read-only from memory/registry)
**Testing**: PHPUnit 12.5 (Unit tests for Command)
**Target Platform**: CLI (Linux/Swoole environment)
**Project Type**: Framework Package (`packages/http-router`)
**Performance Goals**: Instant output (<100ms) for route listing.
**Constraints**: Must rely on `RouteRegistry` for data. Must follow `ServiceProvider` pattern for registration.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **Mandatory Testing**: Confirm that every planned new class/file has a corresponding test task.
  - `RouteListCommand` -> `RouteListCommandTest`
- [x] **Code Quality**: Confirm that the design adheres to SOLID/DRY principles and uses appropriate Design Patterns (Refactoring Guru).
  - Uses Search Command pattern (Console Command) and Service Provider (Strategy/Factory).

## Project Structure

### Documentation (this feature)

```text
specs/018-route-list-command/
├── plan.md              # This file
├── research.md          # Implementation research
├── quickstart.md        # Usage guide
└── tasks.md             # Execution tasks
```

### Source Code

```text
packages/http-router/src/
├── HttpRouterServiceProvider.php  # [NEW] Service Provider
├── Console/
│   └── RouteListCommand.php       # [NEW] Console Command
└── ...

packages/core/src/Foundation/
└── ProviderRepository.php         # [MODIFY] Register new provider
```

**Structure Decision**: Adheres to existing package structure. Introduces `Console` namespace in `http-router` to separate CLI concerns.
