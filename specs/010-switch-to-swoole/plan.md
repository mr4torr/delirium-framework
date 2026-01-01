# Implementation Plan: Switch to Swoole

**Branch**: `010-switch-to-swoole` | **Date**: 2026-01-01 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/010-switch-to-swoole/spec.md`

## Summary

Migrate the underlying asynchronous engine from `openswoole/core` to the upstream `ext-swoole` extension. This change involves updating composer dependencies, refactoring namespace references in `Core` and `HttpRouter` packages, and verifying the `SwoolePsrAdapter` behavior. This aligns the framework with the Static PHP CLI (SPC) ecosystem for future binary distribution.

## Technical Context

**Language/Version**: PHP 8.4
**Primary Dependencies**: `ext-swoole` (replacing `openswoole/core`), `swoole/ide-helper` (dev)
**Testing**: PHPUnit (Integration tests needing Swoole environment)
**Target Platform**: Linux (Swoole requirement)
**Performance Goals**: Parity with OpenSwoole implementation.
**Constraints**: Must run in environments where `extension_loaded('swoole')` is true.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Swoole-First & Async Native**: ✅ This change reinforces this principle by moving to the primary upstream implementation.
- **II. Design Patterns Driven**: ✅ N/A (Infrastructure change).
- **III. Stateless & Memory Safe**: ✅ Swoole follows the same memory model.
- **IV. Strict Contracts & Typing**: ✅ No change to PSR interfaces.
- **V. Modular Architecture**: ✅ No change.
- **VI. Attribute-Driven Meta-Programming**: ✅ No change.

## Project Structure

### Documentation (this feature)

```text
specs/010-switch-to-swoole/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
└── tasks.md             # Phase 2 output (to be generated)
```

### Source Code

```text
packages/
├── core/
│   ├── composer.json    # Update dependencies
│   └── src/
│       └── Application.php  # Refactor namespaces
└── http-router/
    ├── composer.json    # Update dependencies
    └── src/
        └── Bridge/
            └── SwoolePsrAdapter.php # Refactor namespaces & type hints
```

## Implementation Phases

### Phase 1: Dependency Updates
1. Uninstall `openswoole/core` from root and packages.
2. Add `ext-swoole` requirement.
3. Add `swoole/ide-helper` to `require-dev`.

### Phase 2: Refactoring
1. Replace `OpenSwoole\Http\Server` -> `Swoole\Http\Server` in `Application.php`.
2. Replace `OpenSwoole\Http\Request` -> `Swoole\Http\Request` in `SwoolePsrAdapter.php`.
3. Replace `OpenSwoole\Http\Response` -> `Swoole\Http\Response` in `SwoolePsrAdapter.php`.

### Phase 3: Verification
1. Run static analysis (Mago/Psalm) to catch type mismatches.
2. Run integration tests with `pecl install swoole` environment.
