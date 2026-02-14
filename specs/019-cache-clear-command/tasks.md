# Tasks: Cache Clear Command

## Phase 1: Setup

- [x] T001 Initialize feature documentation in `specs/019-cache-clear-command/`

## Phase 2: Foundational

- [x] T002 [P] Create `RegenerationListenerInterface.php` in `packages/core/src/Console/Contract/`
- [x] T003 [P] Create `RegenerationRegistry.php` in `packages/core/src/Foundation/Cache/`
- [x] T004 Create unit test for `RegenerationRegistry` in `packages/core/tests/Foundation/Cache/RegenerationRegistryTest.php`

## Phase 3: User Story 1 - Developer clears and warms up cache (P1)

**Story Goal**: Clear `var/cache` and regenerate core bootstrap files.
**Independent Test Criteria**: Running `bin/console cache:clear` clears the directory and recreates `discovery.php` and `dependency-injection.php`.

- [x] T005 [P] [US1] Create `CacheClearCommand.php` in `packages/core/src/Console/Command/`
- [x] T006 [US1] Implement recursive deletion logic in `CacheClearCommand` (MUST ensure root `var/cache` is preserved and created if missing)
- [x] T007 [P] [US1] Implement `DiscoveryRegenerationListener.php` in `packages/core/src/Console/Listener/`
- [x] T008 [P] [US1] Implement `ContainerRegenerationListener.php` in `packages/core/src/Console/Listener/`
- [x] T009 [US1] Register core listeners in `RegenerationRegistry` via `CoreServiceProvider` (or equivalent)
- [x] T010 [US1] Create integration test for `CacheClearCommand` in `packages/core/tests/Console/Command/CacheClearCommandTest.php`

## Phase 4: User Story 2 - Automated Cache Maintenance (P2)

**Story Goal**: Success in non-interactive/CI environments.
**Independent Test Criteria**: Command exits with code 0 on an empty directory and handles permission errors gracefully.

- [x] T011 [US2] Verify `CacheClearCommand` exit codes in automated scenarios (CI compatibility)
- [x] T012 [US2] Verify command behavior when `var/cache` directory is read-only (error handling)

## Phase 5: Polish & Integrations

- [x] T013 Update `OptimizeCommand.php` in `packages/core/src/Console/Command/` to call `cache:clear`
- [x] T014 Update `RouteListCommand.php` in `packages/http-router/src/Console/Command/` to call `cache:clear`
- [x] T015 Run `composer lint` across affected packages
- [x] T016 Verify final implementation via `bin/console cache:clear` (Performance Check: < 5s for 1000 files/10MB)

## Dependencies

- Phase 2 (Foundational) must be completed before Phase 3 (US1).
- Phase 3 (US1) must be completed before Phase 5 (Integrations).

## Parallel Execution Examples

- T002, T003 (Phase 2)
- T005, T007, T008 (Phase 3)

## Implementation Strategy

- **MVP Scope**: Complete Phase 2 and Phase 3. This provides the core functionality (clearing and warming up discovery/DI) which is the primary value.
- **Incremental Delivery**: Phase 4 and 5 add robustness and deep framework integration.
