---
description: "Tasks for Route List Command feature"
---

# Tasks: Route List Command

**Input**: Design documents from `specs/018-route-list-command/`
**Prerequisites**: plan.md, spec.md

## Phase 1: Setup & Foundation

**Purpose**: Register the service provider in the core framework to ensure the command is discovered.

- [x] T001 Register HttpRouterServiceProvider in packages/core/src/Foundation/ProviderRepository.php

## Phase 2: User Story 1 - Developer lists routes (Priority: P1) 🎯 MVP

**Goal**: Enable developers to view all registered routes via console command.

**Independent Test**: Run `bin/console route:list` and verify output table matches defined routes.

### Tests for User Story 1 (MANDATORY)

- [x] T002 [US1] Create unit test for RouteListCommand in packages/http-router/tests/Unit/Console/Command/RouteListCommandTest.php

### Implementation for User Story 1

- [x] T003 [P] [US1] Create HttpRouterServiceProvider in packages/http-router/src/HttpRouterServiceProvider.php
- [x] T004 [P] [US1] Create RouteListCommand in packages/http-router/src/Console/Command/RouteListCommand.php
- [x] T005 [US1] Implement registration logic in packages/http-router/src/HttpRouterServiceProvider.php
- [x] T006 [US1] Implement execute logic in packages/http-router/src/Console/Command/RouteListCommand.php

## Dependencies & Execution Order

1. **Foundational (Phase 1)**: T001 blocks the *auto-discovery* but development of the provider (T003) can happen in parallel. Use manual registration for dev if needed.
2. **User Story 1 (Phase 2)**:
   - T002 (Test) should be written first (TDD).
   - T003 & T004 (Scaffolding) can be done in parallel.
   - T005 & T006 (Logic) depend on scaffolding.

## Implementation Strategy

### MVP First (User Story 1)

1. Complete T001 (Foundation).
2. Write T002 (Test).
3. Implement T003-T006.
4. Verify by running `bin/console route:list`.
