# Tasks: Live Reload

**Branch**: `005-live-reload` | **Spec**: [specs/005-live-reload/spec.md](spec.md)

## Phase 1: Setup
*Goal: Prepare configuration structures.*

- [ ] T001 [US1] P Implement `liveReload` and `watchDirs` properties in `packages/core/src/Options/DebugOptions.php`
- [ ] T002 [US1] P Refactor `public/index.php` to return `ApplicationInterface` instance

## Phase 2: Foundational (DevTools Package)
*Goal: Create the mechanism to watch files and restart processes.*

- [ ] T003 [US1] Create `packages/dev-tools` directory and `composer.json`
- [ ] T004 [US1] Implement `ProcessManager` class in `packages/dev-tools/src/ProcessManager.php`
- [ ] T005 [US1] Implement `Watcher` class in `packages/dev-tools/src/Watcher.php` (scan & debug options integration)
- [ ] T006 [US1] Create `bin/watcher` script to instantiate and run `Delirium\DevTools\Watcher`
- [ ] T007 [US1] Implement restart logic on file change detection (Integration)

## Phase 3: User Story 1 (Active Development)
*Goal: Ensure the developer workflow functions as expected.*

- [ ] T008 [US1] Verify watcher restarts server on modification of `src/` file
- [ ] T009 [US1] Verify watcher restarts server on modification of `packages/` file
- [ ] T010 [US1] Verify watcher ignores `vendor/` and `var/`
- [ ] T011 [US1] Verify graceful shutdown (CTRL+C) terminates child process

## Dependencies

- T003 depends on T001, T002 (needs App instance to read config)
- T006 depends on T003, T001
- T007 depends on T004, T005

## Implementation Strategy
Start by modifying the core `DebugOptions` and `index.php` to expose configuration. Then build the `bin/watcher` script iteratively: first just spawning the process, then adding the file scan loop, and finally connecting the two for the restart behavior.
