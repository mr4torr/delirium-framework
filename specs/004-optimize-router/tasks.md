# Feature Tasks: Optimize Router Scanner

**Feature**: 004-optimize-router
**Status**: Ready for Execution

## Phase 1: Setup
- [ ] T001 Initialize Feature 004 branch and environment
- [ ] T002 Update `packages/http-router/composer.json` to require `psr/http-factory`

## Phase 2: Foundational
- [ ] T003 [P] Create test fixtures for complex PHP files (Deep namespaces, comments, traits) in `packages/http-router/tests/Fixtures/Complex`

## Phase 3: Robust Route Scanning (US1)
**Goal**: Replace regex scanner with robust `token_get_all` logic.

### Tests
- [ ] T004 [US1] Create `AttributeScannerTest` in `packages/http-router/tests/Unit/Scanner/AttributeScannerTest.php` validating fixture discovery

### Implementation
- [ ] T005 [US1] Implement `TokenScanner` logic using `token_get_all` in `packages/http-router/src/Scanner/AttributeScanner.php`
- [ ] T006 [US1] Support detection of Namespace and Class name via tokens
- [ ] T007 [US1] Verify `AttributeScanner` ignores non-class files and interfaces if required
- [ ] T008 [US1] Verify `AttributeScanner` correctly resolves classes in test suite

## Phase 4: Standards-Compliant PSR-7 Usage (US2)
**Goal**: Decouple from Nyholm concrete implementation.

### Implementation
- [ ] T009 [US2] Update `SwoolePsrAdapter` constructor to accept `ServerRequestFactoryInterface` etc. in `packages/http-router/src/Bridge/SwoolePsrAdapter.php`
- [ ] T010 [US2] Refactor `SwoolePsrAdapter::createFromSwoole` to use injected factories
- [ ] T011 [US2] Refactor `SwoolePsrAdapter::emitToSwoole` to rely on generic `ResponseInterface` methods (no change needed usually)
- [ ] T012 [US2] Update `AppFactory` or `Container` config to inject `Nyholm\Psr7\Factory\Psr17Factory` into Adapter

## Phase 5: Finalization
- [ ] T013 Run full static analysis (Mago)
- [ ] T014 Run full test suite (`composer test`)
- [ ] T015 Verify zero direct `new Response()` usage in `http-router` src

## Dependencies
- US2 depends on US1 (lightly, can be parallel).
- T005 (Scanner Refactor) is the core complexity.

## Implementation Strategy
1. **MVP**: Complete US1 (Scanner) first to ensure robustness.
2. **Refactor**: Complete US2 (PSR-7) to clean up technical debt.
