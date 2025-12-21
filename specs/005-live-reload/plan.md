# Implementation Plan: Live Reload

**Branch**: `005-live-reload` | **Date**: 2025-12-20 | **Spec**: [specs/005-live-reload/spec.md](spec.md)
**Input**: Feature specification from `specs/005-live-reload/spec.md`

## Summary

Implement a development tool (`bin/watcher`) that monitors the `src/` and `packages/` directories for file changes and automatically restarts the application server. This ensures that code changes are immediately reflected without manual intervention.

The watcher will:
1.  Introspect the application configuration (via `public/index.php`) to check `DebugOptions`.
2.  If `liveReload` is enabled, spawn the application server as a child process.
3.  Monitor configured directories (`watchDirs`) for modifications.
4.  Terminate and restart the child process upon detecting changes.

## Technical Context

**Language/Version**: PHP 8.4 (CLI)
**Primary Dependencies**: None (Standard Library) or `Swoole\Process` if beneficial.
**Target Platform**: Linux (Standard PHP CLI environment)
**Performance Goals**: < 1s detection-to-restart time.
**Constraints**: Zero external binary dependencies (no `npm` required).

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

*   **I. Swoole-First & Async Native**: The watcher itself runs as a CLI process. While it controls a Swoole server, the watcher's implementation can be synchronous (polling loop) or async (Swoole Timer/Event). We will prefer standard PHP CLI for broad compatibility but leverage Swoole features if they simplify process management.
*   **II. Design Patterns**: The watcher logic will follow clean code practices (Separation of Concerns: `Watcher`, `ProcessManager`).
*   **V. Modular Architecture**: The watcher logic is a development tool. We will modify `DebugOptions` in `packages/core` to hold the configuration. The watcher script will use `packages/dev-tools` (or inline logic) to read this configuration.

## Proposed Changes

### 1. Core Package
#### [MODIFY] [DebugOptions.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/core/src/Options/DebugOptions.php)
- Add `public bool $liveReload = false;`
- Add `public array $watchDirs = ['src', 'packages'];`

### 2. Public Entry Point
#### [MODIFY] [index.php](file:///home/mr4torr/Project/delirium/delirium-framework/public/index.php)
- Refactor to return the `Application` instance instead of voiding on `listen()`.
- Ensure `listen()` is only called if the script is executed directly, allowing the watcher to require it for inspection.

### 3. Dev Tools (New Package or Bin)
#### [NEW] [bin/watcher](file:///home/mr4torr/Project/delirium/delirium-framework/bin/watcher)
- Script that requires `public/index.php`.
- Extracts `DebugOptions`.
- Implements the polling loop and process management.

## Project Structure

### Documentation (this feature)

```text
specs/005-live-reload/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── quickstart.md        # Phase 1 output
└── checkslists/         # Quality checks
```

### Source Code (repository root)

```text
bin/
└── watcher              # [NEW] The executable script
packages/
└── dev-tools/           # [NEW] Optional: if we want to extract logic
    ├── src/
    │   ├── Watcher.php
    │   └── ProcessManager.php
    └── composer.json
```

**Structure Decision**: I will create a new package `packages/dev-tools` to contain the logic, ensuring modularity (Principle V). The `bin/watcher` script will just bootstrap this package.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| New Package `dev-tools`| Encapsulate dev logic | Keeping huge logic in `bin/file` is messy |
