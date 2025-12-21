# Implementation Plan: Response Class Implementation

**Branch**: `009-response-class-implementation` | **Date**: 2025-12-21 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/009-response-class-implementation/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Implement a `Delirium\Http\Response` class extending `Nyholm\Psr7\Response` to provide Developer Experience (DX) improvements while maintaining strict PSR-7 compliance. The key addition is a polymorphic `body(mixed $content)` method that handles serialization (JSON, casting) and stream creation automatically. Additionally, a `Delirium\Http\JsonResponse` class is introduced for explicit JSON responses.

## Technical Context

**Language/Version**: PHP 8.4+
**Primary Dependencies**: `nyholm/psr7`, `psr/http-message`
**Storage**: N/A
**Testing**: `phpunit` (Unit tests for serialization coverage)
**Target Platform**: Linux (OpenSwoole environment)
**Project Type**: Framework / Library
**Performance Goals**: Negligible overhead over native PSR-7 instantiation.
**Constraints**: Must strictly adhere to `Psr\Http\Message\ResponseInterface`.
**Scale/Scope**: Two new core classes.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Core Principles
- [x] **I. Swoole-First**: Compatible with Swoole's response handling via PSR adaptation.
- [x] **II. Design Patterns**: Uses **Decorator/Extension** pattern to enhance standard response.
- [x] **III. Stateless**: Responses are value objects (conceptually).
- [x] **IV. Strict Contracts & Typing**: Implements `ResponseInterface`.
- [x] **V. Modular Architecture**: Part of `packages/core` (or `http-kernel` if split).
- [x] **VI. Attribute-Driven**: N/A

### Development Standards
- [x] **PSR Compliance**: Directly enforces PSR-7.

## Project Structure

### Documentation (this feature)

```text
specs/009-response-class-implementation/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output (N/A)
├── quickstart.md        # Governance Guide
├── contracts/           # Phase 1 output (N/A)
└── tasks.md             # Phase 2 output
```

### Source Code (repository root)

```text
packages/core/src/Http/
├── Response.php         # [NEW] Extended Response class
└── JsonResponse.php     # [NEW] Specialized JSON Response class
```

**Structure Decision**: Placing in `packages/core/src/Http` as these are fundamental HTTP primitives for the framework.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None      |            |                                     |
