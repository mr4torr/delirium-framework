# Feature Specification: Decouple Packages

**Feature Branch**: `016-decouple-packages`
**Created**: 2026-02-01
**Status**: Draft
**Input**: User description: "Assegurar que os pacotes contidos no diretório ./packages sejam agnósticos e independentes, evitando qualquer tipo de dependência entre eles. Na eventual identificação de acoplamentos, será necessário planejar e executar as adequações necessárias para mitigá-los."

## Clarifications

### Session 2026-02-01
- Q: Which mechanism should enforce architectural boundaries? → A: **Deptrac**. We will configure `qossmic/deptrac` with a `depfile.yaml` to strictly enforce layers and fail the build on violations.
- Q: How should we handle shared logic (helpers)? → A: **New Support Package**. Create `packages/support` as a lightweight, dependency-free library for common utilities. Other packages can depend on this without violating decoupling principles.
- Q: How to handle dev-tools dependencies? → A: **Strict One-Way**. `packages/dev-tools` MUST NOT depend on `Core`. It should be a standalone library. `Core` (and others) can `require-dev` it.
- Q: Where should shared contracts/interfaces live? → A: **PSR + Support Fallback**. Use standard PSR interfaces whenever possible. If a custom shared interface is strictly necessary, it MUST be placed in `packages/support` (not a separate contracts package).

## User Scenarios & Testing

### User Story 1 - Standalone Package Usage (Priority: P1)

As a developer, I want to use individual packages like `http-router` or `dependency-injection` in a project without being forced to install the entire `core` framework or other unrelated packages, ensuring my project remains lightweight and flexible.

**Why this priority**: Code decoupling is essential for maintainability, reuse, and preventing technical debt (spaghetti code).

**Independent Test**: Create a separate directory, `composer init`, install only `delirium/http-router` (via local path repository), and verify it works without `delirium/core` code present.

**Acceptance Scenarios**:

1. **Given** a new blank PHP project, **When** I require `delirium/http-router`, **Then** it accepts the dependency and works (classes can be instantiated) without errors related to missing `Delirium\Core` or `Delirium\DI` classes.
2. **Given** the `delirium/dependency-injection` package, **When** I inspect its `imports` and `composer.json`, **Then** it should not reference `Delirium\Core` or `Delirium\Http`.
3. **Given** the `delirium/validation` package, **When** used standalone, **Then** it functions without `Delirium\Core`.

### User Story 2 - Monorepo Integrity (Priority: P2)

As a framework maintainer, I want to ensure that dependencies flow only in one direction (towards the composition layer) so that the codebase remains modular and testable.

**Why this priority**: Prevents circular dependencies that break CI/CD and make refactoring impossible.

**Independent Test**: Run `vendor/bin/deptrac` (or `mago` if integrated) to verify strictly defined layers.

**Acceptance Scenarios**:

1. **Given** the `packages/` directory, **When** analyzing `http-router`, **Then** no usage of `Delirium\Core` namespace exists.
2. **Given** the `packages/` directory, **When** analyzing `dependency-injection`, **Then** no usage of `Delirium\Core` namespace exists.
3. **Given** the `packages/` directory, **When** analyzing `validation`, **Then** no usage of `Delirium\Core` namespace exists.
4. **Given** the `packages/dev-tools` directory, **When** analyzing it, **Then** no dependency on `Delirium\Core` exists in `composer.json` or code.

### Edge Cases

- **Circular Logic**: If `Package A` implements an interface from `Package B`, but `Package B` puts that interface in its `src` and depends on `Package A` for something else.
  - *Resolution*: Prefer PSR interfaces. If a custom interface is needed, move it to `packages/support`.
- **Service Location**: If `http-router` needs to resolve controllers and uses `Core\Container`.
  - *Resolution*: It MUST use `Psr\Container\ContainerInterface`.
- **Global Helpers**: If packages rely on global helper functions defined in `Core`.
  - *Resolution*: Helper functions MUST be moved to `packages/support`.

## Requirements

### Functional Requirements

- **FR-001**: The `packages/http-router` MUST NOT depend on `packages/core`, `packages/dependency-injection`, or `packages/validation` directly. Use PSR interfaces (e.g., `Psr\Container\ContainerInterface`) if integration is needed.
- **FR-002**: The `packages/dependency-injection` MUST NOT depend on `packages/core` or other sibling packages.
- **FR-003**: The `packages/validation` MUST NOT depend on `packages/core` or other sibling packages.
- **FR-004**: The `packages/core` MAY depend on other packages to glue them together, but MUST use abstractions where possible to allow swapping implementations if designed as such.
- **FR-005**: All packages MUST have their own Valid `composer.json` defining their specific dependencies, without relying on the root `composer.json`.
- **FR-006**: The system MUST include a `depfile.yaml` (Deptrac configuration) defining layers for each package and forbidding upward dependencies.
- **FR-007**: A new `packages/support` package MUST be created to house shared utilities (e.g., array/string helpers) with ZERO internal dependencies.
- **FR-008**: The `packages/dev-tools` library MUST be strictly decoupled from `Core`; it can be a dev-dependency of other packages but cannot depend on them.
- **FR-009**: Shared interfaces MUST use standard PSRs where available. Custom shared interfaces MUST be placed in `packages/support`.

### Key Entities

- **Package**: A directory under `packages/` containing a `composer.json` and `src/`.
- **Dependency**: A `use` statement or `require` field in `composer.json` pointing to a sibling package.
- **Layer**: A logical group of classes defined in Deptrac (e.g., `Layer: Http`, `Layer: Core`).

## Success Criteria

### Measurable Outcomes

- **SC-001**: 0 imports of `Delirium\Core` found in `packages/http-router/src`.
- **SC-002**: 0 imports of `Delirium\Core` found in `packages/dependency-injection/src`.
- **SC-003**: 0 imports of `Delirium\DI` found in `packages/http-router/src` (should use `Psr\Container`).
- **SC-004**: `composer validate` passes for all individual package `composer.json` files.
- **SC-005**: `vendor/bin/deptrac` execution returns exit code 0 (no violations).
- **SC-006**: `packages/dev-tools/composer.json` has an empty `require` block (or only external libs).

**Feature Branch**: `[###-feature-name]`
**Created**: [DATE]
**Status**: Draft
**Input**: User description: "$ARGUMENTS"

## User Scenarios & Testing *(mandatory)*

<!--
  IMPORTANT: User stories should be PRIORITIZED as user journeys ordered by importance.
  Each user story/journey must be INDEPENDENTLY TESTABLE - meaning if you implement just ONE of them,
  you should still have a viable MVP (Minimum Viable Product) that delivers value.

  MANDATORY TESTING: Every user story MUST have associated acceptance scenarios/tests.

  Assign priorities (P1, P2, P3, etc.) to each story, where P1 is the most critical.
  Think of each story as a standalone slice of functionality that can be:
  - Developed independently
  - Tested independently
  - Deployed independently
  - Demonstrated to users independently
-->

### User Story 1 - [Brief Title] (Priority: P1)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently - e.g., "Can be fully tested by [specific action] and delivers [specific value]"]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]
2. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

### User Story 2 - [Brief Title] (Priority: P2)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

### User Story 3 - [Brief Title] (Priority: P3)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

[Add more user stories as needed, each with an assigned priority]

### Edge Cases

<!--
  ACTION REQUIRED: The content in this section represents placeholders.
  Fill them out with the right edge cases.
-->

- What happens when [boundary condition]?
- How does system handle [error scenario]?

## Requirements *(mandatory)*

<!--
  ACTION REQUIRED: The content in this section represents placeholders.
  Fill them out with the right functional requirements.
-->

### Functional Requirements

- **FR-001**: System MUST [specific capability, e.g., "allow users to create accounts"]
- **FR-002**: System MUST [specific capability, e.g., "validate email addresses"]
- **FR-003**: Users MUST be able to [key interaction, e.g., "reset their password"]
- **FR-004**: System MUST [data requirement, e.g., "persist user preferences"]
- **FR-005**: System MUST [behavior, e.g., "log all security events"]

*Example of marking unclear requirements:*

- **FR-006**: System MUST authenticate users via [NEEDS CLARIFICATION: auth method not specified - email/password, SSO, OAuth?]
- **FR-007**: System MUST retain user data for [NEEDS CLARIFICATION: retention period not specified]

### Key Entities *(include if feature involves data)*

- **[Entity 1]**: [What it represents, key attributes without implementation]
- **[Entity 2]**: [What it represents, relationships to other entities]

## Success Criteria *(mandatory)*

<!--
  ACTION REQUIRED: Define measurable success criteria.
  These must be technology-agnostic and measurable.
-->

### Measurable Outcomes

- **SC-001**: [Measurable metric, e.g., "Users can complete account creation in under 2 minutes"]
- **SC-002**: [Measurable metric, e.g., "System handles 1000 concurrent users without degradation"]
- **SC-003**: [User satisfaction metric, e.g., "90% of users successfully complete primary task on first attempt"]
- **SC-004**: [Business metric, e.g., "Reduce support tickets related to [X] by 50%"]
