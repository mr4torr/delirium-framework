---
description: "Task list for feature 017-service-discovery"
---

# Tasks: Service Providers & Aliases

**Input**: Design documents from `/specs/017-service-discovery/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, quickstart.md

**Tests**: Tests are **MANDATORY** per Constitution Principle VII. Every new feature or user story MUST be accompanied by unit and/or integration tests.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Establish the contract layer in Support package

- [x] T001 Create `Delirium\Support\ServiceProvider` abstract class in `packages/support/src/ServiceProvider.php` with `register()` and `boot()` methods
- [x] T002 [P] Create `packages/support/composer.json` if not exists and ensure PSR-4 autoloading for `Delirium\Support`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 Create `Delirium\Core\Foundation` directory structure in `packages/core/src/Foundation/`
- [x] T004 [P] Implement `Delirium\Core\Foundation\ProviderRepository` in `packages/core/src/Foundation/ProviderRepository.php` to manage provider registration and loading
- [x] T005 [P] Implement `Delirium\Core\Foundation\AliasLoader` in `packages/core/src/Foundation/AliasLoader.php` to manage `class_alias` registration
- [x] T006 [P] Implement cache persistence logic in `Delirium\Core\Foundation\ProviderRepository` to write/read `var/cache/discovery.php`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Provider Loading (Priority: P1) 🎯 MVP

**Goal**: Enable dynamic loading of Service Providers with environment-specific support

**Independent Test**: Register a provider via `$app->register()`, boot the app, and verify the service is available in the container

### Tests for User Story 1 (MANDATORY) 🛡️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation (TDD discipline)**

- [x] T007 [P] [US1] Create unit test for `ProviderRepository` in `packages/core/tests/Foundation/ProviderRepositoryTest.php`
- [x] T008 [P] [US1] Create integration test for provider registration flow in `packages/core/tests/Integration/ProviderRegistrationTest.php`

### Implementation for User Story 1

- [x] T009 [US1] Add `register(string $provider, string $env = 'all'): void` method to `Delirium\Core\Application` in `packages/core/src/Application.php`
- [x] T009a [US1] Implement class existence validation in `register()` method throwing `\InvalidArgumentException` if provider class not found
- [x] T010 [US1] Integrate `ProviderRepository` into `Application` boot sequence (before `listen()`)
- [x] T011 [US1] Create `Delirium\DevTools\DevToolsServiceProvider` in `packages/dev-tools/src/DevToolsServiceProvider.php` implementing `ServiceProvider`
- [x] T012 [US1] Refactor `Delirium\Core\Console\Kernel` in `packages/core/src/Console/Kernel.php` to remove hardcoded DevTools loading
- [x] T013 [US1] Update `AppFactory` or bootstrap code to register `DevToolsServiceProvider` with `'dev'` environment
- [x] T014 [US1] Verify DevTools commands are available via `bin/console` after migration

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Class Aliases (Priority: P2)

**Goal**: Implement Class Alias support for developer convenience

**Independent Test**: Call `$app->alias('Foo', FooService::class)` and verify `Foo::method()` works

### Tests for User Story 2 (MANDATORY) 🛡️

- [x] T015 [P] [US2] Create unit test for `AliasLoader` in `packages/core/tests/Foundation/AliasLoaderTest.php`
- [x] T016 [P] [US2] Create integration test for alias functionality in `packages/core/tests/Integration/AliasRegistrationTest.php`

### Implementation for User Story 2

- [x] T017 [US2] Add `alias(string $alias, string $class): void` method to `Delirium\Core\Application` in `packages/core/src/Application.php`
- [x] T017a [US2] Implement class existence validation in `alias()` method throwing `\InvalidArgumentException` if target class not found
- [x] T018 [US2] Integrate `AliasLoader` activation into `Application` boot sequence (after providers registered, before boot)
- [x] T019 [US2] Update cache structure in `ProviderRepository` to include aliases in `var/cache/discovery.php`
- [x] T020 [US2] Test alias functionality with a sample alias (e.g., `Route` -> `Delirium\Http\Router`)

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T021 [P] Update `specs/017-service-discovery/quickstart.md` with real usage examples from implementation
- [x] T022 Verify cache generation works correctly (check `var/cache/discovery.php` is created)
- [x] T023 Run full test suite `composer test` to ensure no regressions
- [x] T024 Run `composer lint` to ensure code quality standards
- [x] T025 Run `composer arch` to verify no architectural violations introduced
- [ ] T026 [P] Add example Service Provider in `src/Example/` demonstrating usage pattern

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies
- **Foundational (Phase 2)**: Depends on Setup
- **User Stories (Phase 3+)**: Depend on Foundational
- **Polish (Final Phase)**: Depends on all stories

### User Story Dependencies

- **User Story 1 (P1)**: Independent - can start after Foundational
- **User Story 2 (P2)**: Independent - can start after Foundational (but logically builds on US1 concepts)

### Parallel Opportunities

- T001 and T002 can run in parallel
- T004, T005, T006 can run in parallel (different files)
- T007 and T008 can run in parallel (different test files)
- T009 and T009a are sequential (validation is part of register method)
- T015 and T016 can run in parallel (different test files)
- T017 and T017a are sequential (validation is part of alias method)
- T021, T024, T025, T026 can run in parallel

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (ServiceProvider contract)
2. Complete Phase 2: Foundational (ProviderRepository, AliasLoader, caching)
3. Complete Phase 3: User Story 1 (Provider registration and DevTools migration)
4. **STOP and VALIDATE**: Test provider registration independently
5. Verify DevTools commands work via the new system

### Incremental Delivery

1. Foundation + US1 → Provider system working
2. Add US2 → Alias system working
3. Polish → Production-ready

### Parallel Team Strategy

With multiple developers:
1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1 (Provider system)
   - Developer B: User Story 2 (Alias system)
3. Stories integrate seamlessly via shared `Application` API
