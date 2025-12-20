---
description: "Task list for HTTP Routing Package implementation"
---

# Tasks: HTTP Routing & Attribute Controllers

**Input**: Design documents from `/specs/001-http-routing-pkg/`
**Prerequisites**: plan.md (required), spec.md (required), data-model.md
**Organization**: Tasks are grouped by user story priorities (P1, P2, P3).

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Initialize the package structure and dependencies.

- [x] T001 Create package directory structure in packages/http-router/
- [x] T002 Initialize composer.json with openswoole/core and psr/http-message dependencies
- [x] T003 [P] Configure basic PHPUnit setup in packages/http-router/phpunit.xml
- [x] T004 Define autoloading in packages/http-router/composer.json and update root composer.json
- [x] T026 Define HttpRouterModule entry point in packages/http-router/src/HttpRouterModule.php

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core interfaces and Attribute definitions required by all stories.

- [x] T005 [P] Define RouteAttribute abstract class in packages/http-router/src/Attribute/RouteAttribute.php
- [x] T006 [P] Define Method Attributes (Get, Post, Put, Delete, etc.) in packages/http-router/src/Attribute/Methods.php
- [x] T007 [P] Define Controller Attribute in packages/http-router/src/Attribute/Controller.php
- [x] T008 Define RouterInterface and DispatcherInterface in packages/http-router/src/Contract/
- [x] T009 Create basic Exception classes (RouteNotFoundException, MethodNotAllowedException) in packages/http-router/src/Exception/

**Checkpoint**: Attributes and Interfaces exist.

---

## Phase 3: User Story 1 - Defining Routes with Attributes (Priority: P1) 🎯 MVP

**Goal**: Enable defining routes via Attributes and scanning them.
**Independent Test**: Define a dummy controller, scan it, and verify routes are in the registry.

### Implementation for User Story 1

- [x] T010 [US1] Create RouteRegistry class to hold route definitions in packages/http-router/src/RouteRegistry.php
- [x] T011 [US1] Implement AttributeScanner to read PHP Attributes in packages/http-router/src/Scanner/AttributeScanner.php
- [x] T012 [US1] Implement Router::scan() to populate registry from a directory in packages/http-router/src/Router.php
- [x] T013 [US1] Add unit test for Scanner to verify it correctly extracts paths and methods in packages/http-router/tests/Unit/ScannerTest.php

**Checkpoint**: Can scan a directory and extract route metadata.

---

## Phase 4: User Story 2 - PSR-7 & OpenSwoole Bridge (Priority: P2)

**Goal**: Convert OpenSwoole requests to PSR-7.
**Independent Test**: Mock a Swoole Request, pass to Adapter, assert result is instanceof ServerRequestInterface.

### Implementation for User Story 2

- [x] T014 [US2] Implement ContextAdapterInterface in packages/http-router/src/Contract/ContextAdapterInterface.php
- [x] T015 [US2] Create SwooleStream implementation (PSR-7 Stream) in packages/http-router/src/Bridge/SwooleStream.php
- [x] T016 [US2] Implement SwoolePsrAdapter::createFromSwoole() in packages/http-router/src/Bridge/SwoolePsrAdapter.php
- [x] T017 [US2] Implement SwoolePsrAdapter::emitToSwoole() in packages/http-router/src/Bridge/SwoolePsrAdapter.php
- [x] T018 [US2] Add integration test with mock Swoole objects in packages/http-router/tests/Integration/BridgeTest.php

**Checkpoint**: Can convert objects between Swoole and PSR-7 worlds.

---

## Phase 5: User Story 3 - Route Parameters and Dispatching (Priority: P3)

**Goal**: Dynamic routing with parameters and execution.
**Independent Test**: Register `/users/{id}`, request `/users/42`, verify handler receives `42`.

### Implementation for User Story 3

- [x] T019 [US3] Implement Dispatcher strategy (Regex matching) in packages/http-router/src/Dispatcher/RegexDispatcher.php
- [x] T020 [US3] Update Router to execute Dispatcher::dispatch() in packages/http-router/src/Router.php
- [x] T021 [US3] Implement logic to extract named parameters from URI and pass to handler
- [x] T022 [US3] Add end-to-end test: Define Controller -> Scan -> Dispatch -> result in packages/http-router/tests/Integration/RouterTest.php
- [x] T027 [US3] Add unit tests for Edge Cases (Duplicate routes, 404, 405, Invalid Params) in packages/http-router/tests/Unit/EdgeCaseTest.php

**Checkpoint**: Full flow working.

---

## Phase 6: Polish & Cross-Cutting Concerns

- [x] T023 Code cleanup and strict type checks
- [x] T024 Documentation updates (README.md in package)
- [x] T025 Performance optimization (Route compilation caching)

---

## Dependencies & Execution Order

- **Foundational** blocks **US1**.
- **US1** (Scanning) blocks **US3** (Dispatching) - *Routes must be known to be dispatched.*
- **US2** (Bridge) is independent of dispatch logic but required for the final `handle()` method signature. Can be done in parallel with US1/US3.
