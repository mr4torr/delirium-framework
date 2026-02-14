# Implementation Plan: Console Runner

**Branch**: `012-console-runner` | **Date**: 2026-01-01 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/012-console-runner/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Implement a centralized CLI entry point (`bin/console`) similar to Symfony and Laravel, utilizing `symfony/console` component. This will replace/encapsulate the existing `bin/server` and `bin/watcher` scripts into `server:start` and `server:watch` commands respectively.

## Technical Context

**Language/Version**: PHP 8.4
**Primary Dependencies**: `symfony/console`, `ext-swoole`, `symfony/dependency-injection`
**Testing**: PHPUnit
**Target Platform**: Linux (Swoole environment)
**Project Type**: Framework (Packages: `core`, `dev-tools`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Swoole-First**: `bin/console` itself is CLI, but subcommands like `server` start the Swoole server.
- **Design Patterns**:
    - **Command Pattern**: Directly used via `symfony/console`.
    - **Module**: Commands should be registered/provided by Modules (`Core`, `DevTools`).
- **Strict Cleanup**: N/A for CLI runner itself, but important for long-running `server` command.
- **Strict Types**: Mandatory.

## Project Structure

### Documentation (this feature)

```text
specs/012-console-runner/
├── plan.md              # This file
├── research.md          # N/A (Standard Symfony Console implementation)
├── data-model.md        # N/A
├── quickstart.md        # Usage guide for bin/console
├── tasks.md             # Generated tasks
```

### Source Code

```text
root/
├── bin/
│   └── console          # [NEW] Entry point script (chmod +x)
├── packages/
│   ├── core/
│   │   ├── composer.json # [MODIFY] Add symfony/console suggested/required?
│   │   └── src/
│   │       └── Console/
│   │           ├── Kernel.php       # [NEW] Application wrapper/bootloader
│   │           └── Command/
│   │               └── ServerCommand.php # [NEW]
│   └── dev-tools/
│       ├── composer.json # [MODIFY] Add symfony/console dependency
│       └── src/
│           └── Console/
│               └── Command/
│                   └── ServerWatchCommand.php # [NEW]
└── composer.json        # [MODIFY] Require symfony/console
```

**Structure Decision**: Commands are distributed across packages. `bin/console` will boot the application (Container) and retrieve registered commands.

## Complexity Tracker

N/A
