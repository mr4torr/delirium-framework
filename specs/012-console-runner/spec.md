# Feature Specification: Console Runner

**Feature Branch**: `012-console-runner`
**Created**: 2026-01-01
**Status**: Draft
**Input**: User description: "implmentar solução semelhante ao bin/console utilizado pelo Symfony e artisan pelo Laravel. Utilize o bin/server e bin/watcher como  comando a serem implementados"

## User Scenarios & Testing

### User Story 1 - Centralized CLI Management (Priority: P1)

As a developer, I want a single entry point (`bin/console`) to manage my application tasks so that I don't have to remember multiple script names.

**Why this priority**: Streamlines the developer experience (DX) and aligns with industry standards (Symfony/Laravel).

**Independent Test**: Can be tested by running `php bin/console` and verifying it lists available commands.

**Acceptance Scenarios**:

1. **Given** a terminal in the project root, **When** I run `php bin/console`, **Then** I see a list of available commands and the application version.
2. **Given** a terminal, **When** I run `php bin/console --help`, **Then** I see usage instructions.

---

### User Story 2 - Server Management command (Priority: P1)

As a developer, I want to start the application server using the console command so that I can easily boot the application with options.

**Why this priority**: Essential for running the application; consolidates the `bin/server` functionality into the framework.

**Independent Test**: Can be verified by running `php bin/console server` and checking if the application accepts HTTP requests.

**Acceptance Scenarios**:

1. **Given** the console application, **When** I run `php bin/console server:start` (or `server`), **Then** the Swoole HTTP server starts and listens for requests.
2. **Given** the server is running, **When** I send a request to the configured port, **Then** I receive a valid HTTP response.

---

### User Story 3 - Development Watcher (Priority: P2)

As a developer, I want a `server:watch` command that automatically restarts the server when code changes so that I can develop rapidly without manual restarts.

**Why this priority**: Critical for developer productivity (Live Reload feature).

**Independent Test**: Can be verified by running `php bin/console server:watch` and modifying a file to see the restart trigger.

**Acceptance Scenarios**:

1. **Given** the console application, **When** I run `php bin/console server:watch`, **Then** it starts the server and watches for file changes.
2. **Given** the watcher is running, **When** I modify a source file in `src/`, **Then** the server process automatically restarts.

---

### Edge Cases

- **Port in Use**: When `server:start` is run on a port already taken, it should display a user-friendly error message and exit with a non-zero code.
- **Watcher Syntax Error**: If a watched file has a syntax error that prevents the server from booting, the watcher (running via `server:watch`) should attempt to restart but log the error without crashing the watcher process itself.
- **Missing Dependencies**: If `bin/console` is run without `vendor/autoload.php` (e.g. before composer install), it should fail gracefully (standard PHP behavior, but good to note).

---

## Requirements

### Functional Requirements

- **FR-001**: System MUST provide a `bin/console` executable script in the project root.
- **FR-002**: The console application MUST allow registering and executing commands.
- **FR-003**: System MUST implement a `server` command that encapsulates the logic currently in `bin/server` (starting the application).
  - Constraint: This command MUST be implemented in the `delirium/core` package.
- **FR-004**: System MUST implement a `server:watch` command that encapsulates the logic currently in `bin/watcher` (file watching and process management).
  - Constraint: This command MUST be implemented in the `delirium/dev-tools` package.
- **FR-005**: The `server:watch` command MUST support configuration for watched directories (as currently supported or via options).
- **FR-006**: Commands MUST utilize standard output/error streams for feedback.

### Key Entities

- **Console Application**: The central container for commands.
- **Command**: An individual task.
  - `ServerCommand` (in `delirium/core`)
  - `ServerWatchCommand` (in `delirium/dev-tools`)

## Success Criteria

### Measurable Outcomes

- **SC-001**: Developer can execute `php bin/console` and receive immediate feedback (command list) in under 100ms.
- **SC-002**: `php bin/console server` starts the web server successfully, matching current `bin/server` behavior.
- **SC-003**: `php bin/console server:watch` successfully detects file changes and restarts the server process.
- **SC-004**: The architecture supports adding future commands (e.g., database migrations) without modifying the `bin/console` entry script significantly.

## Clarifications

### Session 2026-01-01

- **User Request**: Rename the `watcher` command to `server:watch`.
  - **Change**: Updated User Story 3, FR-004, FR-005, SC-003 to reference `server:watch` instead of `watcher`.

- **User Request**: `server` command in `core` package, `server:watch` in `dev-tools` package.
  - **Change**: Added constraints to FR-003 and FR-004.
