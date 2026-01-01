# Tasks: Switch to Swoole

**Feature**: Switch to Swoole (010-switch-to-swoole)
**Status**: Pending

## Phase 1: Setup

*Goal: Prepare the environment and dependencies for the switch.*

- [x] T001 Remove `openswoole/core` dependency from root composer.json
- [x] T002 Add `ext-swoole` dependency to root composer.json
- [x] T003 Make same dependency changes in `packages/core/composer.json`
- [x] T004 Make same dependency changes in `packages/http-router/composer.json`
- [x] T005 Add `swoole/ide-helper` to `require-dev` in root composer.json and update lock files

## Phase 2: Foundational

*Goal: There are no blocking architectural changes, so this phase is empty.*

*(Skipped)*

## Phase 3: Replace Runtime Engine (US1)

*Goal: Migrate the underlying async engine from OpenSwoole to upstream Swoole (P1).*
*Independent Test: Can be validated by inspecting dependencies and booting the application server in a Swoole-enabled environment.*

- [x] T006 [P] [US1] Replace `OpenSwoole\Http\Server` with `Swoole\Http\Server` in `packages/core/src/Application.php`
- [x] T007 [P] [US1] Replace `OpenSwoole\Http\Request` with `Swoole\Http\Request` in `packages/core/src/Application.php` (if referenced)
- [x] T008 [P] [US1] Replace `OpenSwoole\Http\Response` with `Swoole\Http\Response` in `packages/core/src/Application.php` (if referenced)
- [x] T009 [P] [US1] Replace `OpenSwoole\Http\Request` import and usage with `Swoole\Http\Request` in `packages/http-router/src/Bridge/SwoolePsrAdapter.php`
- [x] T010 [P] [US1] Replace `OpenSwoole\Http\Response` import and usage with `Swoole\Http\Response` in `packages/http-router/src/Bridge/SwoolePsrAdapter.php`
- [x] T011 [US1] Verify and update any other `OpenSwoole` references in `packages/core` (grep check)
- [x] T012 [US1] Verify and update any other `OpenSwoole` references in `packages/http-router` (grep check)

## Phase 4: Verify Application Stability (US2)

*Goal: Ensure no regressions in routing or request handling (P1).*
*Independent Test: Run the full test suite.*

- [x] T013 [US2] Update any integration tests in `packages/core/tests` that mock or use `OpenSwoole` classes
- [x] T014 [US2] Update any integration tests in `packages/http-router/tests` that mock or use `OpenSwoole` classes
- [x] T015 [US2] Run comprehensive test suite with `pecl install swoole` active environment
- [x] T016 [US2] Create manual verification script `bin/verify-swoole.php` that asserts `Swoole\Http\Server` can be instantiated

## Final Phase: Polish

- [x] T017 Clean up any leftover `openswoole` configuration in `composer.json` scripts or Dockerfiles if applicable
- [x] T018 Update `README.md` requirements section to specify `ext-swoole` instead of `openswoole`

## Dependencies

- Phase 1 blocks Phase 3
- Phase 3 blocks Phase 4
- T001-T004 can be done in parallel
- T006-T010 can be done in parallel

## Parallel Execution Examples

- **US1**: T006 (Application.php) and T009 (SwoolePsrAdapter.php) are in different packages and can be refactored simultaneously.

## Implementation Strategy

We will update dependencies first to ensure the environment is correct (Phase 1). Then we will perform a 'search and replace' style refactor on the core packages (Phase 3). Finally, we run the tests to verify stability (Phase 4).
