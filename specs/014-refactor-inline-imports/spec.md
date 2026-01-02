# Feature Specification: Refactor Namespace Imports

**Feature Branch**: `014-refactor-inline-imports`
**Created**: 2026-01-02
**Status**: Draft
**Input**: User description: "Ajustar código existente para que não utilize a importação inline, deve ser utilizado o use do php"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Codebase Refactoring for Import Standards (Priority: P1)

The development team needs the codebase to adhere to the new "Code Quality Standards" (Principle VIII) regarding explicit imports to improve maintainability and readability.

**Why this priority**: Compliance with the Constitution is mandatory. Inline FQNs make code harder to read and manage.

**Independent Test**: Can be verified by running a linter or regex search for backslashes inside function bodies and confirming tests pass.

**Acceptance Scenarios**:

1. **Given** a PHP file with inline FQNs like `new \Delirium\Foo\Bar()`, **When** the refactor is applied, **Then** the file should have `use Delirium\Foo\Bar;` at the top and `new Bar()` in the method.
2. **Given** a file with name collisions (e.g. `\Other\Bar`), **When** refactored, **Then** it should use an alias `use Other\Bar as OtherBar;` and `new OtherBar()`.
3. **Given** the refactored codebase, **When** tests are run, **Then** all tests must pass (no functionality regressions).

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System (Codebase) MUST use PHP `use` statements for class dependencies.
- **FR-002**: System code MUST NOT contain inline Fully Qualified Names (FQNs) inside class methods or functions, except for:
    - Native PHP root functions if configured that way (though `use function` is preferred by some, strictly usually applies to Classes/Interfaces/Enums). *Assumption: Rule applies primarily to Classes/Interfaces/Enums.*
    - String literals that happen to look like namespaces (though code should minimize this).
- **FR-003**: The refactoring MUST NOT alter the runtime behavior of the application.

### Key Entities

- N/A (Code Refactoring)

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of PHP files in `packages/` comply with the Import Ordering rule.
- **SC-002**: Zero regressions in the test suite (`composer test` passes).
- **SC-003**: Manual or automated inspection reveals zero instances of `new \Foo\Bar` patterns inside methods.
