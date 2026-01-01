# Task List: SPC Compiler Package

**Feature**: SPC Compiler (011-spc-compiler)
**Spec**: [spec.md](./spec.md)
**Plan**: [plan.md](./plan.md)

## Phase 1: Setup & Package Creation

*Goal: Initialize the `compile` package and install dependencies.*

- [x] T001 Create `packages/compile` directory structure (`src/`, `tests/`, `composer.json`)
- [x] T002 Define `delirium/compile` in `packages/compile/composer.json` including `crazywhalecc/static-php-cli` as dev dependency
- [ ] T003 Register `packages/compile` in root `composer.json` repositories and require it
- [ ] T004 Run `composer update` to install `static-php-cli` and the new package

## Phase 2: Foundational

*Goal: Implement configuration and basic entry point.*

- [x] T005 Implement `Delirium\Compile\Config\CompileConfig` class in `packages/compile/src/Config/CompileConfig.php`
- [x] T006 Create `bin/compile` script executable at project root to bootstrap the build process
- [x] T007 Implement skeleton `Delirium\Compile\Command\CompileCommand` class

## Phase 3: Generate Application PHAR (US1)

*Goal: Bundle the application into a `.phar` file using a staging strategy (P1).*
*Independent Test: `php build/delirium.phar` runs the app.*

- [x] T008 [US1] Implement `Delirium\Compile\Service\StagingManager` to handle copying project to `build/staging`
- [x] T009 [US1] Add logic to `StagingManager` to run `composer install --no-dev` in staging directory
- [ ] T010 [US1] Implement `Delirium\Compile\Service\PharBuilder` to traverse staging dir and build PHAR
- [ ] T011 [US1] Wire `StagingManager` and `PharBuilder` into `CompileCommand`
- [ ] T012 [US1] Verify that `bin/compile` generates a valid `build/delirium.phar` (Integration Test: check boots and NO dev-deps)

## Phase 4: Build Static Binary (Micro SAPI) (US2)

*Goal: Generate the standalone executable using SPC (P1).*
*Independent Test: `./build/delirium` runs without PHP installed.*

- [x] T013 [US2] Implement `Delirium\Compile\Service\SpcBuilder` to wrapper `vendor/bin/spc`
- [x] T014 [US2] Add method to `SpcBuilder` to check/run `spc download` for required extensions
- [x] T015 [US2] Add method to `SpcBuilder` to run `spc build --build-micro` combining the PHAR
- [x] T016 [US2] Update `CompileCommand` to invoke `SpcBuilder` after PHAR generation
- [x] T017 [US2] [P] Create manual verification script `bin/verify-build.sh` to check binary exists, is executable, and runs `php -m` to assert required extensions listed in FR-006

## Phase 4b: Docker-Based Compilation (Refinement)

*Goal: Enable static binary generation via Docker to bypass local dependency issues.*

- [x] T021 [US2] Update `CompileConfig` to support `useDocker` option
- [x] T022 [US2] Update `CompileCommand` to accept `--use-docker` flag
- [x] T023 [US2] Update `SpcBuilder` to support running compilation inside `php:8.4-cli-alpine` container (with dynamic deps)
- [x] T024 [US2] Update `README.md` with Docker/Local build instructions

## Final Phase: Polish

*Goal: Cleanup and documentation.*

- [x] T018 Cleanup `build/staging` directory after successful build
- [x] T019 Update `README.md` with build instructions
- [x] T020 Ensure `.gitignore` ignores `build/` and `packages/compile/vendor/`

## Dependencies

- US1 (PHAR) is a prerequisite for US2 (Binary).
- Setup (Phase 1) is required for all.

## Implementation Strategy

We will build the system incrementally:
1.  **Setup**: Get the package structure right.
2.  **PHAR**: Focus on getting a working `.phar` first. This validates the code bundling logic.
3.  **Binary**: Add the heavy lifting of SPC download/compilation on top of the PHAR.
