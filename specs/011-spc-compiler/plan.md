# Implementation Plan: SPC Compiler Package

**Branch**: `011-spc-compiler` | **Date**: 2026-01-01 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/011-spc-compiler/spec.md`

## Summary

This feature introduces a build system to generate a standalone binary for the Delirium Framework application.
It involves creating a `packages/compile` package that orchestrates:
1. Copying the source to a staging directory to install only production dependencies (no-dev).
2. Bundling the production-ready PHP application into a `.phar` file.
3. Using `static-php-cli` (SPC) to download PHP sources and build a "Micro SAPI" executable.
4. Combining the Micro executable with the PHAR to produce a single binary.

## Technical Context

**Language/Version**: PHP 8.4
**Primary Dependencies**: `crazywhalecc/static-php-cli` (Dev), `ext-swoole`, `ext-phar`
**Storage**: N/A (Build artifacts only)
**Testing**: Integration test (run generated binary)
**Target Platform**: Linux x86_64
**Project Type**: CLI Tool / Build System

## Constitution Check

*GATE: Passed.*
- **Modular Architecture**: We are creating a dedicated `packages/compile` module.
- **Swoole-First**: The resulting binary enables Swoole-native deployment.
- **Strict Typing**: All new code will reference strict types.

## Project Structure

### Documentation (this feature)

```text
specs/011-spc-compiler/
├── plan.md              # This file
├── research.md          # Research findings
├── data-model.md        # Entities (Config, Artifacts)
├── quickstart.md        # Usage guide
└── tasks.md             # Tasks list
```

### Source Code

```text
bin/
└── compile              # Entry point script
packages/
└── compile/
    ├── composer.json    # Package definition
    ├── src/
    │   ├── Command/
    │   │   └── CompileCommand.php
    │   ├── Service/
    │   │   ├── PharBuilder.php
    │   │   └── SpcBuilder.php
    │   │   └── StagingManager.php  # Handles staging dir logic
    │   └── Config/
    │       └── CompileConfig.php
    └── tests/
```

## Implementation Phases

### Phase 1: Setup & Package Creation
- Create `packages/compile` structure.
- Initialize `packages/compile/composer.json`.
- Add `crazywhalecc/static-php-cli` dependency.
- Register package in root `composer.json`.

### Phase 2: Staging & PHAR Builder
- Implement `StagingManager` to copy files and run `composer install --no-dev`.
- Implement `PharBuilder` service consuming the staging dir.
- Support filtering/including paths (`src`, `vendor`, `config`, `public`).
- Create `bin/compile` wrapper.

### Phase 3: SPC Integration
- Implement `SpcBuilder` service.
- Handle `spc download` (with caching check).
- Handle `spc build` (Micro SAPI).
- Orchestrate the fusion of Micro + PHAR.

### Phase 4b: Docker-Based Compilation (Refinement)
- Add `--use-docker` flag to `bin/compile`.
- Refactor `SpcBuilder` to run `spc build` inside `crazywhalecc/static-php-cli:alpine`.
- Mount project root to container.
- Update `README.md` fallback instructions.

### Phase 5: Verification
- Verify the binary boots correctly.
- Add CI/CD smoke test (optional for now, but good practice).

