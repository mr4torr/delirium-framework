# Feature 007: Use PSR Interfaces in Method Injection

**Branch**: `007-use-psr-interfaces`
**Spec**: [spec.md](./spec.md)
**Plan**: [plan.md](./plan.md)

## Phase 1: Setup

*No specific setup tasks required.*

## Phase 2: Foundational

*No foundational tasks required.*

## Phase 3: User Story 1 - Injection Interoperability ([US1])

**Goal**: Standardize on PSR interfaces for injection.

**Independent Test**: Route `GET /psr-test` should return 200 via `PsrInjectionController`.

- [x] T001 [US1] Create `src/Example/PsrInjectionController.php` to demonstrate PSR injection
- [x] T002 [US1] Refactor `src/Example/ExampleController.php` to use `ServerRequestInterface` instead of concrete Request
- [x] T003 [US1] Audit `src/` for usage of `OpenSwoole\Http\Request` or `Response` and refactor if needed

## Phase 4: Polish & Verification

- [x] T004 Create integration test `tests/Feature/PsrInjectionTest.php` to verify controller injection
- [x] T005 Verify `packages/http-router` resolvers correctly identify PSR interfaces (Audit/Manual)
- [x] T006 Run tests `vendor/bin/phpunit`

## Implementation Strategy
- **MVP**: Enable `PsrInjectionController` and verify it works. Refactor `ExampleController`.

## Dependencies
- None.
