# Implementation Plan: Refactor Namespace Imports

**Branch**: `014-refactor-inline-imports` | **Date**: 2026-01-02 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/014-refactor-inline-imports/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Refactor all PHP files in `packages/` to replace inline Fully Qualified Names (FQNs) with explicit `use` statements at the top of each file, in compliance with Constitution Principle VIII (Code Quality Standards - Import Ordering). This is a zero-behavior-change refactor validated by the existing test suite.

## Technical Context

**Language/Version**: PHP 8.4
**Primary Dependencies**: Composer (autoload PSR-4), existing codebase dependencies
**Storage**: N/A (Code refactoring only)
**Testing**: PHPUnit (`composer test`)
**Target Platform**: Linux server (Swoole runtime)
**Project Type**: Monorepo with multiple packages
**Performance Goals**: N/A (Refactor should not impact runtime performance)
**Constraints**: Zero test regressions, no runtime behavior changes
**Scale/Scope**: All PHP files in `packages/` directory (~50-100 files estimated)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **Mandatory Testing**: Existing test suite validates refactor (no new classes).
- [x] **Code Quality**: Refactor enforces Constitution Principle VIII (Import Ordering).

## Project Structure

### Documentation (this feature)

```text
specs/014-refactor-inline-imports/
├── plan.md              # This file
├── research.md          # Phase 0: Refactoring strategy
├── data-model.md        # N/A (no data model)
├── quickstart.md        # N/A (internal refactor)
├── contracts/           # N/A (no API changes)
└── tasks.md             # Phase 2: Task breakdown
```

### Source Code (repository root)

```text
packages/
├── core/
│   ├── src/
│   │   ├── AppFactory.php        # [REFACTOR]
│   │   └── ...
│   └── tests/
├── http-router/
│   ├── src/
│   │   ├── Dispatcher/           # [REFACTOR]
│   │   ├── Resolver/             # [REFACTOR]
│   │   ├── Message/              # [REFACTOR]
│   │   └── ...
│   └── tests/
└── [other packages]/
```

**Structure Decision**: This is a horizontal refactor across all existing packages. No new directories or files are created. All `.php` files in `packages/*/src/` and `packages/*/tests/` will be scanned and refactored to use `use` statements instead of inline FQNs.

## Complexity Tracking

> **No violations**: This refactor enforces constitutional compliance.
