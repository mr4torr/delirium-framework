---
description: "Task list for Console Runner feature implementation"
---

# Tasks: Console Runner

**Input**: Design documents from `/specs/012-console-runner/`
**Prerequisites**: plan.md (required), spec.md (required)

**Tests**: Tests are OPTIONAL - only included if explicitly requested.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Update root `composer.json` to require `symfony/console`
- [x] T002 Update `packages/core/composer.json` to require `symfony/console`
- [x] T003 Update `packages/dev-tools/composer.json` to require `symfony/console`
- [x] T004 Run `composer update` to install dependencies

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T005 Create `packages/core/src/Console/Kernel.php` to boot Console Application
- [x] T006 Create `bin/console` entry point script (and chmod +x)

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Centralized CLI Management (Priority: P1) 🎯 MVP

**Goal**: Provide a single entry point (`bin/console`) to manage application tasks.

**Independent Test**: Run `php bin/console` and confirm it lists available commands.

### Implementation for User Story 1

- [x] T007 [US1] Implement logic in `bin/console` to instantiate Kernel and handle input
- [x] T008 [US1] Register `Kernel` service in `Delirium\Core\AppFactory` or DI container if needed
- [x] T009 [US1] Verify `php bin/console list` outputs standard Symfony Console help

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Server Management command (Priority: P1)

**Goal**: Start the application server using the console command (`server`).

**Independent Test**: Run `php bin/console server` and verify the application accepts HTTP requests.

### Implementation for User Story 2

- [x] T010 [US2] Create `packages/core/src/Console/Command/ServerCommand.php`
- [x] T011 [US2] Implement `execute` method in `ServerCommand` with logic from `bin/server`
- [x] T012 [US2] Register `ServerCommand` in `packages/core/src/Console/Kernel.php` or `packages/core/src/App.module.php` (if modular)
- [x] T013 [US2] Verify `php bin/console server` boots the Swoole server

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - Development Watcher (Priority: P2)

**Goal**: Automatically restart the server when code changes using `server:watch`.

**Independent Test**: Run `php bin/console server:watch` and verify server restarts on file change.

### Implementation for User Story 3

- [x] T014 [US3] Create `packages/dev-tools/src/Console/Command/ServerWatchCommand.php`
- [x] T015 [US3] Implement `execute` method in `ServerWatchCommand` with logic from `bin/watcher`
- [x] T016 [US3] Configure `ServerWatchCommand` to use `Delirium\DevTools\Watcher`
- [x] T017 [US3] Register `ServerWatchCommand` in application (likely via `DevTools` package discovery or manual config)

**Checkpoint**: All user stories should now be independently functional

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T018 Remove legacy `bin/server` script
- [x] T019 Remove legacy `bin/watcher` script
- [x] T020 Run `quickstart.md` validation to ensure commands match docs

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
- **Polish (Final Phase)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Depends on Foundational
- **User Story 2 (P1)**: Depends on Foundational (and US1 implicitly for running via console)
- **User Story 3 (P2)**: Depends on Foundational (and US1 implicitly)

### Implementation Strategy

1. **Setup & Foundation**: Get `symfony/console` installed and `bin/console` executable running.
2. **US1 & US2**: Deliver core server running capability via new CLI.
3. **US3**: Add developer experience (watcher).
4. **Cleanup**: Remove old scripts only after verification.
