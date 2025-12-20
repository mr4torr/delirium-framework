# Tasks: Core Application Factory

**Branch**: `002-core-app-factory` | **Spec**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

## Phase 1: Setup (Project & Foundation)

Initial contracts and directory structure required for the core factory.

- [X] T001 Create core contract `Delirium\Core\Contract\ApplicationInterface` in `src/Contract/ApplicationInterface.php` <!-- id: 0 -->
- [X] T002 Create infrastructure for attributes in `src/Attribute/` <!-- id: 1 -->

## Phase 2: Foundational Components (Container & Attributes)

Must be completed before any logic can be implemented.

- [X] T003 Create `#[AppModule]` Attribute class in `src/Attribute/AppModule.php` with `imports`, `controllers`, `providers` <!-- id: 2 -->
- [X] T004 Create PSR-11 `Delirium\Container\Container` in `src/Container/Container.php` <!-- id: 3 -->
- [X] T005 [P] Create unit test for Container in `tests/Unit/ContainerTest.php` <!-- id: 4 -->

**Checkpoint**: DI Container works and Attributes are defined.

## Phase 3: User Story 1 - Application Bootstrap (P1)

Goal: Initialize application with basic settings (Port, CORS).

- [X] T006 [US1] Create `Delirium\Core\Application` class in `src/Core/Application.php` implementing `ApplicationInterface` <!-- id: 5 -->
- [X] T007 [US1] Implement `Application::listen()` wrapping OpenSwoole Server <!-- id: 6 -->
- [X] T008 [US1] Create `Delirium\Core\DeliriumFactory` class in `src/Core/DeliriumFactory.php` <!-- id: 7 -->
- [X] T009 [US1] Implement `DeliriumFactory::create(ModuleClass)` to return Application instance <!-- id: 8 -->
- [X] T010 [US1] Implement `AppOptions` configuration object to handle Port and CORS settings <!-- id: 9 -->
- [X] T020 [US1] Implement CORS headers logic in `Application` server based on configuration <!-- id: 19 -->

**Dependencies**: T004 (Container)

## Phase 4: User Story 3 - Modular Architecture (P1)

Goal: Recursive module scanning and dependency wiring.

- [X] T011 [US3] Implement `ModuleScanner` (or private method) in `DeliriumFactory` for attribute reading <!-- id: 10 -->
- [X] T012 [US3] Implement recursive DFS traversal in scanner to handle `imports` (App -> Public -> Private) <!-- id: 11 -->
- [X] T013 [US3] Connect scanned `providers` to `Container` registration <!-- id: 12 -->
- [X] T014 [US3] Connect scanned `controllers` to `Router` registration (requires HttpRouter integration) <!-- id: 13 -->
- [X] T015 [US3] Implement cycle detection/prevention in module scanner <!-- id: 14 -->
- [X] T016 [P] [US3] Create `HierarchyTestModule` fixture structure in `tests/Fixtures/Hierarchy/` <!-- id: 15 -->
- [X] T017 [US3] Create integration test in `tests/Integration/RecursiveModuleTest.php` verifying deep nested routes <!-- id: 16 -->

## Phase 5: Polish & Cross-Cutting

- [X] T018 [US1] Create `public/index.php` bootstrap file <!-- id: 17 -->
- [X] T019 [US2] Update `composer.json` autoloading if necessary for new `Core` and `Container` namespaces <!-- id: 18 -->

## Additional Requirements (Safety Net)

- [X] T020 [US1] Implement CORS headers logic in `Application` server based on configuration <!-- id: 21 -->

## Implementation Strategy

1. **MVP**: T001-T009. Basic server that boots.
2. **Feature**: T011-T014. Enable attributes and recursion.
3. **Polish**: T018.

## Parallel Execution

- T005 (Container Test) can start after T004.
- T016 (Fixtures) can start anytime.
