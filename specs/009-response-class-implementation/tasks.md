# Feature 009: Response Class Implementation

**Branch**: `009-response-class-implementation`
**Spec**: [spec.md](./spec.md)
**Plan**: [plan.md](./plan.md)

## Phase 1: Setup

*No complex setup required. Verifying directory structure.*

- [x] T001 Verify `packages/core/src/Http` directory exists

## Phase 2: Foundational

*No blocking foundational tasks.*

## Phase 3: User Story 1 - Simplified Response Creation ([US1])

**Goal**: Implement `Response` and `JsonResponse` classes to simplify response handling.

**Independent Test**: Unit tests verifying `body()` method behavior and JSON serialization.

- [x] T002 [US1] Create unit test `packages/core/tests/Http/ResponseTest.php` for `Delirium\Http\Response`
- [x] T003 [US1] Implement `Delirium\Http\Response` class in `packages/core/src/Http/Response.php`
- [x] T004 [US1] Create unit test `packages/core/tests/Http/JsonResponseTest.php` for `Delirium\Http\JsonResponse`
- [x] T005 [P] [US1] Implement `Delirium\Http\JsonResponse` class in `packages/core/src/Http/JsonResponse.php`

## Phase 4: Polish & Verification

- [x] T006 Create integration test `tests/Feature/ResponseClassTest.php` demonstrating controller usage
- [x] T007 Run all tests `vendor/bin/phpunit`

## Implementation Strategy
- **MVP**: Helper classes for Response.
- **TDD**: Unit tests are created before/alongside implementation to ensure logic coverage (FR-004, FR-005).

## Dependencies
- US1 is self-contained. `JsonResponse` might extend `Response` or `Nyholm\Psr7\Response`.
