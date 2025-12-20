# Tasks: Dependency Injection

**Feature**: 003-dependency-injection
**Spec**: [spec.md](spec.md)
**Plan**: [plan.md](plan.md)
**Status**: Planning

## Phase 1: Setup & Infrastructure

- [ ] T001 Initialize `packages/dependency-injection` directory structure
- [ ] T002 Initialize `packages/dependency-injection/composer.json` with PSR-4 autoloading for `Delirium\DI` and `symfony/dependency-injection` dependency
- [ ] T003 Update root `composer.json` to include new package paths and dependencies
- [ ] T004 Create `Delirium\DI\ContainerBuilder` skeleton class in `packages/dependency-injection/src/ContainerBuilder.php`
- [ ] T005 Update `packages/core/composer.json` to depend on `delirium/dependency-injection`

## Phase 2: Foundational DI (Constructor Injection & Caching)

**User Story**: US1 (Constructor Injection)
**Goal**: Basic container that autowires services and dumps to file.

- [ ] T006 [US1] Implement `build()` method in `Delirium\DI\ContainerBuilder` to initialize Symfony ContainerBuilder
- [ ] T007 [US1] Implement `dump()` logic in `Delirium\DI\ContainerBuilder` using `PhpDumper` to `var/cache/dependency-injection.php`
- [ ] T008 [US1] Update `Delirium\Core\AppFactory` to use `Delirium\DI\ContainerBuilder` instead of legacy logic
- [ ] T009 [US1] Implement logic in `AppFactory` to load from cached container file if it exists (Prod/Dev logic)
- [ ] T010 [US1] Verify Constructor Injection works by creating a test Controller with a Service dependency

## Phase 3: Method Injection (Function Injection)

**User Story**: US2 (Method Injection)
**Goal**: Allow injection into Controller action methods.

- [ ] T011 [US2] Update `Delirium\Core\Router` or `Dispatcher` to use Container for method argument resolution
- [ ] T012 [US2] Ensure route parameters (e.g., `{id}`) take precedence over or coexist with Service injection
- [ ] T013 [US2] Verify Method Injection works: Controller method `(int $id, Service $svc)` receives both correctly

## Phase 4: Property Injection

**User Story**: US3 (Property Injection)
**Goal**: Allow `#[Inject]` on properties (public/private).

- [ ] T014 [US3] Create `Delirium\DI\Attribute\Inject` attribute class
- [ ] T015 [US3] Create `Delirium\DI\Compiler\PropertyInjectionPass` skeleton
- [ ] T016 [US3] Implement logic in `PropertyInjectionPass` to find `#[Inject]` and configure setters/properties
- [ ] T017 [US3] Register `PropertyInjectionPass` in `ContainerBuilder`
- [ ] T018 [US3] Verify Property Injection on private properties via test case

## Phase 5: Implicit Registration (Discovery)

**User Story**: US4 (Implicit Registration)
**Goal**: Auto-register services found in Controllers.

- [ ] T019 [US4] Create `Delirium\DI\Compiler\DiscoveryPass` or extend `ContainerBuilder` scanning logic
- [ ] T020 [US4] Implement reflection logic to scan Controller constructors and `#[Route]` methods for type-hints
- [ ] T021 [US4] Register discovered classes as autowired services if not already present
- [ ] T022 [US4] Verify implicit registration ensures services work without `providers` array in Module

## Phase 6: Refactoring & Cleanup

**Goal**: Remove legacy code and ensure standards.

- [ ] T023 Refactor `Delirium\Core\Container\Container` to act as adapter or remove if direct usage is replaced
- [ ] T024 Run static analysis (Mago/PHPStan) on new package
- [ ] T025 Run full integration suite to ensure no regressions

## Dependencies

- T001-T005 must be done before T006
- T006-T010 (Container) is prerequisite for all injection types
- T011-T013 (Method Injection) requires working Container
- T014-T018 (Property Injection) requires working Container
- T019-T022 (Implicit Registration) builds on basic Container logic
- Phases 3, 4, 5 can technically run in parallel, but implicit registration (Phase 5) benefits from Method Injection logic being settled (checking method signatures).

## Implementation Strategy

We will build the dedicated package first, ensuring it passes unit tests, then integrate it into Core.
