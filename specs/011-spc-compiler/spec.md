# Feature Specification: SPC Compiler Package

**Feature Branch**: `011-spc-compiler`
**Created**: 2026-01-01
**Status**: Draft
**Input**: User description: "Create a Compile package for SPC binary generation..."

## Clarifications

### Session 2026-01-01
- Q: Should the compiler support custom source directories beyond defaults? -> A: Yes, support a configurable list of extra paths (Option A).
- Q: How should `static-php-cli` be integrated? -> A: Use as a Composer library/dependency (Option C).
- Q: Where should the compile entry point live? -> A: `bin/compile` wrapper delegating to package logic (Option A).
- Q: How to handle dev dependencies in the binary? -> A: Despite being in vendor (during dev), they MUST NOT be included in the final binary/phar.
- Q: How to securely exclude dev dependencies? -> A: Use a staging directory strategy (copy -> install --no-dev -> pack) (Option A).

## User Scenarios & Testing

### User Story 1 - Generate Application PHAR (Priority: P1)

As a release manager, I want to compile the entire project (source code and dependencies) into a single `.phar` file so that it can be distributed or embedded into a binary.

**Why this priority**: Prerequisite for generating the final static binary.

**Independent Test**: Can be tested by running the PHAR directly with standard PHP CLI.

**Acceptance Scenarios**:

1. **Given** the project source code, **When** the compile command is run, **Then** a `delirium.phar` file is created in the `build/` directory.
2. **Given** the generated PHAR, **When** executed with `php build/delirium.phar`, **Then** the application boots correctly.

---

### User Story 2 - Build Static Binary (Micro SAPI) (Priority: P1)

As a user, I want to generate a standalone executable binary using Static PHP CLI (SPC) that contains the PHP runtime + extensions + application code, so I can run it on a server without installing PHP or extensions manually.

**Why this priority**: Goal of the feature (ease of distribution).

**Independent Test**: Execute the generated binary on a clean Linux environment (or Docker container) without PHP installed.

**Acceptance Scenarios**:

1. **Given** the SPC build tools are configured, **When** the compile command is run with binary flag, **Then** a standalone executable is created in `build/`.
2. **Given** the binary, **When** executed, **Then** it starts the application server successfully.

## Requirements

### Functional Requirements

- **FR-001**: System MUST provide a new package `packages/compile`.
- **FR-002**: The `compile` package MUST require `crazywhalecc/static-php-cli` as a development dependency via Composer.
- **FR-003**: System MUST provide a binary script `bin/compile` that triggers the build process.
- **FR-004**: The build process MUST first bundle the application into a `.phar` (using `box` or custom script).
- **FR-005**: The build process MUST combine the `.phar` with the SPC Micro SAPI key to generate the final binary.
- **FR-006**: The binary MUST include the following extensions: `ctype`, `iconv`, `dom`, `openssl`, `curl`, `pcntl`, `mbstring`, `tokenizer`, `xml`, `filter`, `json`, `phar`, `posix`, `zlib`, and `swoole`.
- **FR-007**: Generated artifacts (`.phar` and binary) MUST be placed in the `build/` directory at the project root.
- **FR-008**: The build command MUST support defining additional source directories/files to include in the compilation (defaulting to `src`, `vendor`, `public`).
- **FR-009**: The build process MUST exclude `require-dev` packages from the bundled `vendor` directory, ensuring only production dependencies are distributed.

### Key Entities

- **Compiler**: The service responsible for orchestrating the build steps (PHAR generation -> SPC download -> Binary fusion).
- **Artifact**: The resulting files (PHAR, Binary).

## Success Criteria

### Measurable Outcomes

- **SC-001**: `build/delirium` (binary) exists and is executable.
- **SC-002**: Binary size is within reasonable limits (e.g., < 100MB).
- **SC-003**: Binary runs on a standard Linux x86_64 system without external PHP dependencies.
