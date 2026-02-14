# Feature Specification: Cache Clear Command

**Feature Branch**: `019-cache-clear-command`
**Created**: 2026-02-14
**Status**: Draft
**Input**: User description: "implementar o comando `cache:clear` no pacote packages/core ele deve remover os arquivos gerados dentro de var/cache e logo após deve ser regerado os arquivos discovery.php, dependency-injection.php e outros que forem gerados no futuro"

## Clarifications

### Session 2026-02-14

- Q: Os arquivos de cache mencionados devem ser gerados internamente ou por meio de ouvintes/comandos registrados? → A: Execução baseada em ouvintes (Listeners). O comando `cache:clear` deve disparar a regeneração através de uma lista de handlers registrados.
- Q: O comando `cache:clear` deve ser integrado a outros comandos? → A: Sim. Os comandos `optimize` e `route:list` devem executar o `cache:clear` automaticamente antes de suas rotinas principais.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Developer clears and warms up cache (Priority: P1)

As a developer, I want to clear all cached files and immediately regenerate the core bootstrap files to ensure the system is ready and using the latest configuration.

**Why this priority**: Clearing cache is a fundamental maintenance task for framework developers to resolve staled state issues. Instant regeneration (warming up) ensures that subsequent requests do not suffer from the performance hit of the first-time generation.

**Independent Test**: Can be fully tested by running `bin/console cache:clear`. It delivers value by providing a clean state and a warmed-up cache in one step.

**Acceptance Scenarios**:

1. **Given** that `var/cache/` contains several files (e.g., `discovery.php`, `dependency-injection.php`), **When** I run `bin/console cache:clear`, **Then** the command should report that the cache was cleared and successfully regenerated via registered listeners.
2. **Given** that the cache was cleared, **When** I inspect the `var/cache/` directory, **Then** it should contain fresh versions of `discovery.php` and `dependency-injection.php`.

---

### User Story 2 - Automated Cache Maintenance (Priority: P2)

As a DevOps engineer, I want the `cache:clear` command to be usable in CI/CD pipelines to ensure a clean state before deployments.

**Why this priority**: Supports automation and reliability of deployments.

**Independent Test**: Running the command in a non-interactive environment should complete successfully.

**Acceptance Scenarios**:

1. **Given** a deployment script, **When** `bin/console cache:clear` is executed, **Then** it should return a successful exit code (0) even if the cache directory was already empty.

---

### Edge Cases

- **What happens when `var/cache` is not writable?** The system should display a clear error message and exit with a non-zero code.
- **What happens if a listener fails?** If a regeneration listener fails, the command should report the failure but continue with other listeners if possible, and exit with a non-zero code.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a console command named `cache:clear` within the `packages/core` package.
- **FR-002**: System MUST delete all files and subdirectories located within the `var/cache/` directory.
- **FR-003**: System MUST preserve the `var/cache/` directory itself (do not delete the root cache folder).
- **FR-004**: System MUST trigger the regeneration of `discovery.php` by calling its corresponding registered listener.
- **FR-005**: System MUST trigger the regeneration of `dependency-injection.php` by calling its corresponding registered listener.
- **FR-006**: System MUST handle scenarios where the cache directory does not exist by creating it before attempting regeneration.
- **FR-007**: System MUST provide feedback to the user about which files were cleared and which listeners were successfully executed.
- **FR-008**: The `optimize` command MUST call `cache:clear` before executing its own optimization logic.
- **FR-009**: The `route:list` command MUST call `cache:clear` before executing its own listing logic.

### Key Entities *(include if feature involves data)*

- **Cache Manager**: A service (or logic within the command) responsible for filesystem operations on `var/cache`.
- **Regeneration Listener**: An interface or contract that packages implement to provide their cache warming logic.
- **Regeneration Registry**: A mechanism to discover, register, and execute all `Regeneration Listener` implementations.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All files previously existing in `var/cache` are removed during the "clear" phase.
- **SC-002**: `discovery.php` and `dependency-injection.php` are present in `var/cache` after the command finishes.
- **SC-003**: The command execution completes in under 5 seconds under normal conditions.
- **SC-004**: The command returns exit code 0 on success and a non-zero code on failure.
