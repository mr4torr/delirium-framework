# Tasks: Code Quality Improvements (015)

## Phase 1: Setup
- [x] T001 Create feature branch `015-code-quality-improvements`
- [x] T002 Initialize specification documents (`spec.md`)

## Phase 2: Foundational (Core Refactoring)
> **Goal**: Extract responsibilities from `AppFactory` into specialized factories to adhere to SRP.

- [x] T003 [US1] [P] Implement `ModuleScanner` in `packages/core/src/Module/ModuleScanner.php`
- [x] T004 [US1] [P] Implement `ContainerFactory` in `packages/core/src/Container/ContainerFactory.php`
- [x] T005 [US1] Test `ModuleScanner` in `packages/core/tests/Unit/Module/ModuleScannerTest.php`
- [x] T006 [US1] Test `ContainerFactory` in `packages/core/tests/Unit/Container/ContainerFactoryTest.php`
- [x] T007 [US1] Refactor `AppFactory` to delegate responsibilities in `packages/core/src/AppFactory.php`

## Phase 3: Router Refactoring
> **Goal**: Decompose `RegexDispatcher` into `Matcher` and `Invoker` components for better maintenance and testability.

- [x] T008 [US2] Implement `RouteMatcherInterface` in `packages/http-router/src/Routing/Matcher/RouteMatcherInterface.php`
- [x] T009 [US2] [P] Implement `RegexRouteMatcher` in `packages/http-router/src/Routing/Matcher/RegexRouteMatcher.php`
- [x] T010 [US2] [P] Implement `ControllerInvoker` in `packages/http-router/src/Invoker/ControllerInvoker.php`
- [x] T011 [US2] Test `RegexRouteMatcher` in `packages/http-router/tests/Unit/Routing/Matcher/RegexRouteMatcherTest.php`
- [x] T012 [US2] Test `ControllerInvoker` in `packages/http-router/tests/Unit/Invoker/ControllerInvokerTest.php`
- [x] T013 [US2] Refactor `RegexDispatcher` to coordinator role in `packages/http-router/src/Dispatcher/RegexDispatcher.php`

## Phase 4: Verification & Polish
- [x] T014 Run full test suite `composer test` to ensure no regressions
- [x] T015 Verify `bin/console` boot sequence works with new factories
- [x] T016 Check static analysis `composer lint` for new files
