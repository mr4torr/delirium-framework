# Feature Specification: Optimize Router Scanner and PSR-7 Usage

**Feature Branch**: `004-optimize-router`  
**Created**: 2025-12-20  
**Status**: Draft  
**Input**: User description: "Realize uma análise do arquivo packages/http-router/src/Scanner/AttributeScanner.php e verifique a viabilidade de utilizarmos um pacote como o roave/better-reflection, de modo a garantir a conformidade com o padrão PSR-4. Além disso, avalie a possibilidade de otimizar o uso do pacote nyholm/psr7 no projeto http-router, considerando que ele já está em conformidade com os padrões PSR-7, PSR-17 e PSR-18"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Robust Route Scanning (Priority: P1)

As a framework user, I want the Attribute Scanner to reliably detect `#[Controller]` and `#[Route]` attributes using robust parsing logic, so that my routes are registered correctly regardless of file formatting.

**Why this priority**: The current regex-based scanner is fragile and may fail with valid PHP code (e.g., complex namespaces, comments before namespace).

**Independent Test**:
- Create a set of Controller files with various valid PHP structures (different formatting, comments, traits).
- Verify the Scanner detects them all correctly.

**Acceptance Scenarios**:

1. **Given** a Controller class with extensive comments and unusual whitespace, **When** the scanner runs, **Then** it correctly identifies the class and registers the routes.
2. **Given** a directory with non-class PHP files or traits, **When** the scanner runs, **Then** it ignores them without error.

---

### User Story 2 - Standards-Compliant PSR-7 Usage (Priority: P2)

As a framework developer, I want the `http-router` package to use PSR-17 Factories instead of direct `Nyholm\Psr7` class instantiation (where appropriate), so that the code is loosely coupled and follows best practices.

**Why this priority**: Ensures long-term maintainability and easier swapping of PSR-7 implementations if needed.

**Independent Test**:
- Search codebase for `new Response(...)` or `new ServerRequest(...)`.
- Verify they are replaced by Factory calls throughout the package.

**Acceptance Scenarios**:

1. **Given** the `http-router` codebase, **When** analyzed, **Then** no direct instantiation of PSR-7 implementations (like `new Nyholm\Psr7\Response`) exists outside of Factories/Adapters.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The `AttributeScanner` MUST identify PHP classes in a directory structure that adhere to PSR-4.
- **FR-002**: The `AttributeScanner` MUST correctly parse namespaces and class names without relying on fragile regex (e.g., using `roave/better-reflection` or robust token parsing).
- **FR-003**: The `http-router` package MUST depend on `psr/http-factory` interfaces for creating HTTP messages.
- **FR-004**: The `http-router` package MUST strictly use `Nyholm\Psr7\Factory\Psr17Factory` (or injected factories) for instantiation.

### Key Entities

- **AttributeScanner**: The service responsible for iterating files and finding Route attributes.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of valid PSR-4 Controller classes in the test suite are discovered by the Scanner.
- **SC-002**: Zero (0) direct instantiations of `Nyholm\Psr7\Response` or `ServerRequest` in the `http-router` source code (excluding tests/factories).
- **SC-003**: The Scanner successfully processes complex PHP files (e.g., files with multiple namespaces or conditional classes) without throwing parsing errors.
