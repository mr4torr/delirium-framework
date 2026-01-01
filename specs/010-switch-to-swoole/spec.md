# Feature Specification: Switch to Swoole

**Feature Branch**: `010-switch-to-swoole`
**Created**: 2026-01-01
**Status**: Draft
**Input**: User description: "Ajuste o projeto para utilizar Swoole em vez do OpenSwoole"

## User Scenarios & Testing

### User Story 1 - Replace Runtime Engine (Priority: P1)

As a framework developer, I want to migrate the underlying async engine from OpenSwoole to upstream Swoole so that I can leverage its specific features (e.g. compatibility with Static PHP CLI) and broader ecosystem support.

**Why this priority**: This is a foundational change required to proceed with future deployment goals (binary generation).

**Independent Test**: Can be validated by inspecting dependencies and booting the application server in a Swoole-enabled environment.

**Acceptance Scenarios**:

1. **Given** the project configuration, **When** dependencies are listed, **Then** `openswoole/core` is absent and `ext-swoole` is required.
2. **Given** the application codebase, **When** analyzed, **Then** no active code references `OpenSwoole\*` namespaces.
3. **Given** the HTTP server class, **When** instantiated, **Then** it utilizes `Swoole\Http\Server` correctly.

---

### User Story 2 - Verify Application Stability (Priority: P1)

As a user, I expect the application to behave exactly as before after the switch, without regressions in routing or request handling.

**Why this priority**: A platform switch must not break existing application logic.

**Independent Test**: Run the full test suite.

**Acceptance Scenarios**:

1. **Given** the migrated application, **When** running the existing test suite, **Then** all tests pass.
2. **Given** a running server instance, **When** sending a valid HTTP request, **Then** a standard expected response is returned.

## Requirements

### Functional Requirements

- **FR-001**: The project MUST depend on `ext-swoole` (PECL extension) instead of `openswoole/core`.
- **FR-002**: The project MUST NOT reference `OpenSwoole` namespaces in runtime code.
- **FR-003**: The internal HTTP Server implementation MUST map Swoole's `Request` and `Response` objects to the framework's PSR representations correctly, adjusting for API differences if any.
- **FR-004**: Development tools in `composer.json` MUST include `swoole/ide-helper`.
- **FR-005**: All existing HTTP routing and handling logic MUST function identically on the Swoole engine.
- **FR-006**: Continuous Integration configurations MUST install `swoole` extension instead of `openswoole`.

### Key Entities

- **Engine**: The underlying C-extension providing async I/O (switched to Swoole).
- **HttpServer**: The PHP class wrapping the engine's server.

## Success Criteria

### Measurable Outcomes

- **SC-001**: 0 occurrences of string "OpenSwoole" in `src/` and `composer.json`.
- **SC-002**: Application boots in a CLI environment with `swoole` extension enabled.
- **SC-003**: 100% of unit and integration tests pass.
