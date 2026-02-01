---
description: "Task list for feature 016-decouple-packages"
---

# Tasks: Decouple Packages

**Input**: Design documents from `/specs/016-decouple-packages/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, quickstart.md

**Tests**: Tests are **MANDATORY** per Constitution Principle VII. Every new feature or user story MUST be accompanied by unit and/or integration tests.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Install deptrac as a dev-dependency in root composer.json
- [x] T002 Create root depfile.yaml with initial Deptrac configuration (layers and collectors)
- [x] T003 [P] Create packages/support directory with composer.json and src structure

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T004 [P] Move 'dev-tools' out of 'imports' in core/composer.json to require-dev

---

## Phase 3: User Story 1 - Standalone Package Usage (Priority: P1) 🎯 MVP

**Goal**: Enable individual packages to be used without the core framework.

**Independent Test**: Install `http-router` in a clean project and use it.

### Tests for User Story 1 (MANDATORY) 🛡️

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation (TDD discipline)**

- [x] T005 [P] [US1] Create test case to verify Support package independence in packages/support/tests/ArchitectureTest.php

### Implementation for User Story 1

- [x] T006 [P] [US1] Implement core helpers (Arr, Str) in packages/support/src/ (if needed by extraction)
- [x] T006a [P] [US1] Create unit tests for Arr, Str helpers in packages/support/tests/Unit/
- [x] T007 [US1] Refactor packages/http-router to use local Support helpers instead of Core dependencies
- [x] T008 [US1] Refactor packages/dependency-injection to use local Support helpers/contracts instead of Core
- [x] T009 [US1] Refactor packages/validation to use local Support helpers instead of Core
- [x] T010 [US1] Update packages/http-router/composer.json to require delirium/support and remove delirium/core
- [x] T011 [US1] Update packages/dependency-injection/composer.json to require delirium/support and remove delirium/core
- [x] T012 [US1] Update packages/validation/composer.json to require delirium/support and remove delirium/core

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - Monorepo Integrity (Priority: P2)

**Goal**: Enforce architectural boundaries via CI.

**Independent Test**: specific `deptrac` ruleset passes.

### Tests for User Story 2 (MANDATORY) 🛡️

- [x] T013 [P] [US2] Create specific test configuration for deptrac in depfile.yaml (ruleset definition)

### Implementation for User Story 2

- [x] T014 [P] [US2] Configure Deptrac Ruleset: Allow Support -> Vendor
- [x] T015 [P] [US2] Configure Deptrac Ruleset: Allow Http -> Support, Vendor
- [x] T016 [P] [US2] Configure Deptrac Ruleset: Allow Core -> All Layers (Glue)
- [x] T017 [P] [US2] Configure Deptrac Ruleset: Forbid Http -> Core
- [x] T018 [P] [US2] Configure Deptrac Ruleset: Forbid Di -> Core
- [x] T019 [US2] Run Deptrac analysis and fix any remaining violations in packages/core/src/
- [x] T020 [US2] Add 'composer arch' script to root composer.json

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T021 [P] Verify packages/dev-tools contains no Core imports
- [x] T022 Update quickstart.md with actual usage examples from implementation
- [x] T023 Run full test suite (phpunit) to ensure refactoring broke nothing
- [x] T024 Verify all composer.json files are valid (composer validate)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies
- **Foundational (Phase 2)**: Depends on Setup
- **User Stories (Phase 3+)**: Depend on Foundational
- **Polish (Final Phase)**: Depends on all stories

### User Story Dependencies

- **User Story 1 (P1)**: Independent.
- **User Story 2 (P2)**: Dependent on US1 refactoring to pass analysis.

### Parallel Opportunities

- T006, T007, T008, T009 can be parallelized by multiple devs.
- T014-T018 (Deptrac rules) can be configured in parallel.

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Setup Support package.
2. Decouple Http-Router (most critical).
3. Validate standalone usage.

### Incremental Delivery

1. Foundation + Support Pkg.
2. Refactor packages 1 by 1.
3. Enforce with Deptrac.
