# Implementation Plan: Decouple Packages

**Branch**: `016-decouple-packages` | **Date**: 2026-02-01 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/016-decouple-packages/spec.md`

## Summary

This feature aims to decouple the `delirium-framework` packages (`http-router`, `dependency-injection`, `validation`) from `core` and each other to facilitate standalone usage and maintain a clean architecture. It introduces a new `packages/support` package for shared utilities, enforces architectural boundaries using `Deptrac`, and ensures `dev-tools` remains a strict one-way dependency.

## Technical Context

**Language/Version**: PHP 8.4+
**Primary Dependencies**: `qossmic/deptrac`, `delirium/core`, `delirium/http-router`, `delirium/dependency-injection`, `delirium/validation`, `delirium/support` (new).
**Storage**: File system for `depfile.yaml` cache/config.
**Testing**: `phpunit` (unit/integration), `deptrac` (architectural validation).
**Target Platform**: Linux (Swoole environment).
**Project Type**: Monorepo PHP Framework.
**Performance Goals**: N/A (Build-time check).
**Constraints**: Zero circular dependencies allowed in `src/`.
**Scale/Scope**: affects all 5+ packages.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **Mandatory Testing**: New `Support` package requires unit tests. Deptrac integration is a form of architectural testing.
- [x] **Code Quality**: Design enforces SOLID (SRP at package level) and avoids coupling. Adheres to Design Patterns (Modular Architecture).
- [x] **Swoole-First**: Decoupling does not negatively impact Swoole compatibility.

## Project Structure

### Documentation (this feature)

```text
specs/016-decouple-packages/
├── plan.md              # This file
├── research.md          # Architectural decisions
├── dependency-graph.md  # (Data Model) Package relationships
├── quickstart.md        # Guide for Deptrac and Support pkg usage
└── tasks.md             # Implementation tasks
```

### Source Code (repository root)

```text
packages/
├── core/
├── http-router/
├── dependency-injection/
├── validation/
├── dev-tools/
└── support/             # [NEW] Shared utilities
    ├── src/
    │   ├── Arr.php
    │   ├── Str.php
    │   └── Contract/
    └── composer.json
depfile.yaml             # [NEW] Architectural rules
```

**Structure Decision**: Monorepo with a new `support` leaf package. `depfile.yaml` in root.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| N/A | | |

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: [e.g., Python 3.11, Swift 5.9, Rust 1.75 or NEEDS CLARIFICATION]
**Primary Dependencies**: [e.g., FastAPI, UIKit, LLVM or NEEDS CLARIFICATION]
**Storage**: [if applicable, e.g., PostgreSQL, CoreData, files or N/A]
**Testing**: [e.g., pytest, XCTest, cargo test or NEEDS CLARIFICATION]
**Target Platform**: [e.g., Linux server, iOS 15+, WASM or NEEDS CLARIFICATION]
**Project Type**: [single/web/mobile - determines source structure]
**Performance Goals**: [domain-specific, e.g., 1000 req/s, 10k lines/sec, 60 fps or NEEDS CLARIFICATION]
**Constraints**: [domain-specific, e.g., <200ms p95, <100MB memory, offline-capable or NEEDS CLARIFICATION]
**Scale/Scope**: [domain-specific, e.g., 10k users, 1M LOC, 50 screens or NEEDS CLARIFICATION]

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

[Gates determined based on constitution file]
- [ ] **Mandatory Testing**: Confirm that every planned new class/file has a corresponding test task.
- [ ] **Code Quality**: Confirm that the design adheres to SOLID/DRY principles and uses appropriate Design Patterns (Refactoring Guru).

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
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
│   ├── [Feature].module.php
│   ├── [Feature].controller.php
│   ├── [Feature].service.php
│   └── Dto/
└── Shared/
    ├── Infrastructure/
    └── Domain/

```

**Structure Decision**: [Document the selected structure and reference the real
directories captured above]

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
