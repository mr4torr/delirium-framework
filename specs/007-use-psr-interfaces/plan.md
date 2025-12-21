# Implementation Plan: Use PSR Interfaces in Method Injection

**Branch**: `007-use-psr-interfaces` | **Date**: 2025-12-21 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/007-use-psr-interfaces/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Refactor the `src/` directory (user-land code) to use standard PSR interfaces (`Psr\Container\ContainerInterface`, `Psr\Http\Message\ServerRequestInterface`) instead of concrete implementations or non-standard classes for method injection. This confirms and reinforces the framework's commitment to PSR compliance.

## Technical Context

**Language/Version**: PHP 8.4+
**Primary Dependencies**: `symfony/dependency-injection`, `nyholm/psr7`, `psr/container`, `psr/http-message`
**Storage**: N/A
**Testing**: `phpunit` (Integration tests for dependency injection)
**Target Platform**: Linux (OpenSwoole environment)
**Project Type**: Framework / Library
**Performance Goals**: Negligible impact (standard interface resolution)
**Constraints**: Must maintain backward compatibility for now, but strictly encourage PSR usage.
**Scale/Scope**: Refactoring `src/` directory and adding 1-2 integration tests.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Core Principles
- [x] **I. Swoole-First**: Feature uses PSR abstraction which is adapted from Swoole requests via `SwoolePsrAdapter`.
- [x] **II. Design Patterns**: Directly supports "Interfaces First" principle.
- [x] **III. Stateless**: No state changes.
- [x] **IV. Strict Contracts & Typing**: Enforces strict typing using standard PSR interfaces.
- [x] **V. Modular Architecture**: Respects existing module structure.
- [x] **VI. Attribute-Driven**: Compatible with attribute-based injection (`#[Inject]`, `#[MapRequestPayload]`).

### Development Standards
- [x] **PSR Compliance**: Strongly enforces PSR-7, PSR-11.

## Project Structure

### Documentation (this feature)

```text
specs/007-use-psr-interfaces/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output (Empty/N/A)
├── quickstart.md        # This file
├── contracts/           # Phase 1 output (Empty/N/A)
└── tasks.md             # Phase 2 output
```

### Source Code (repository root)

```text
src/
├── Example/
│   ├── ExampleController.php  # [MODIFY] Update method signature
│   └── PsrInjectionController.php # [NEW] Test controller
packages/
└── http-router/
    └── src/
        └── Resolver/
            └── ContainerServiceResolver.php # [VERIFY/MODIFY] Robust interface check
```

**Structure Decision**: Standard library structure. No new modules needed.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None      |            |                                     |
