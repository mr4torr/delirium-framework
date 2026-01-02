# Tasks: Refactor Namespace Imports

**Feature**: `014-refactor-inline-imports` | **Status**: Complete

## Phase 1: Setup
*Goal: Prepare environment for refactoring.*

- [x] T001 Verify test suite passes cleanly before starting refactor
- [x] T002 Verify PHP Linting tools (or confirm manual regex approach)

## Phase 2: Foundational
- N/A (No shared foundation needed for this horizontal refactor)

## Phase 3: User Story 1 - Refactor Packages (Priority: P1)
*Goal: Ensure all packages adhere to Constitution Principle VIII (Import Ordering) without regressions.*
*Independent Test: `composer test` passes after each package refactor. Linter/Regex confirms no inline FQNs.*

### Package 1: HTTP Router (Highest Complexity)
- [x] T003 [US1] Scan and refactor `packages/http-router/src/Attribute/` (Attributes usually simple)
- [x] T004 [US1] Scan and refactor `packages/http-router/src/Contract/` (Interfaces usually simple)
- [x] T005 [P] [US1] Scan and refactor `packages/http-router/src/Message/` (Response/Request helpers)
- [x] T006 [P] [US1] Scan and refactor `packages/http-router/src/Resolver/` (Resolvers have complex deps)
- [x] T007 [P] [US1] Scan and refactor `packages/http-router/src/Dispatcher/` (Core logic)
- [x] T008 [US1] Scan and refactor `packages/http-router/src/Router.php` and `RouteRegistry.php`
- [x] T009 [US1] Run `composer test` for `http-router` package to verify no regressions

### Package 2: Core
- [x] T010 [P] [US1] Scan and refactor `packages/core/src/AppFactory.php` (Main entry)
- [x] T011 [P] [US1] Scan and refactor remaining files in `packages/core/src/`
- [x] T012 [US1] Run `composer test` for `core` package verification

### Package 3: Validation
- [x] T013 [P] [US1] Scan and refactor `packages/validation/src/`
- [x] T014 [US1] Run `composer test` for `validation` package verification

### Package 4: Dependency Injection
- [x] T015 [P] [US1] Scan and refactor `packages/dependency-injection/src/`
- [x] T016 [US1] Run `composer test` for `dependency-injection` package verification

### Package 5: Tests (Refactor Test Files too)
- [x] T017 [P] [US1] Scan and refactor `packages/http-router/tests/`
- [x] T018 [P] [US1] Scan and refactor `packages/core/tests/`
- [x] T019 [P] [US1] Scan and refactor `packages/validation/tests/`
- [x] T020 [P] [US1] Scan and refactor `packages/dependency-injection/tests/`

## Final Phase: Verification
- [x] T021 Run full project suite `composer test`
- [x] T022 Manual grep search for `new \\` or `extends \\` to ensure 100% compliance

## Dependencies

1. **Setup**: T001 must pass.
2. **Refactor**: Packages can be refactored in parallel, but `http-router` and `core` are most critical.

## Implementation Strategy
- **Incremental**: Check package-by-package.
- **TDD-ish**: Run tests frequently (T009, T012, T014, T016) to catch syntax errors or namespace resolution issues immediately.
