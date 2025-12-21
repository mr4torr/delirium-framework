# Feature 008: Third-Party Interface Abstraction strategy

**Branch**: `008-interface-abstraction`
**Spec**: [spec.md](./spec.md)
**Plan**: [plan.md](./plan.md)

## Phase 1: Governance Setup

- [x] T001 Update Constitution with Interface Abstraction Rule in `.specify/memory/constitution.md`
- [/] T002 [US1] Create `Delirium\DI\Contract\CompilerPassInterface` in `packages/dependency-injection/src/Contract/CompilerPassInterface.php`

## Phase 2: Foundational

*No foundational tasks required.*

## Phase 3: User Story 1 - Interface Governance ([US1])

**Goal**: Abstract third-party dependencies behind framework interfaces.

**Independent Test**: verify `DiscoveryPass` implements `Delirium\DI\Contract\CompilerPassInterface` instead of the Symfony one directly.

- [x] T002 [US1] Create `Delirium\DI\Contract\CompilerPassInterface` in `packages/dependency-injection/src/Contract/CompilerPassInterface.php`
- [x] T003 [US1] Refactor `DiscoveryPass` to implement new interface in `packages/dependency-injection/src/Compiler/DiscoveryPass.php`
- [x] T004 [US1] Refactor `PropertyInjectionPass` to implement new interface in `packages/dependency-injection/src/Compiler/PropertyInjectionPass.php`
- [x] T005 [US1] Update `ContainerBuilder` to accept new interface in `packages/dependency-injection/src/ContainerBuilder.php`

## Phase 4: Polish & Verification

- [x] T006 Verify all tests pass with refactored interfaces via `vendor/bin/phpunit`

## Implementation Strategy
- **MVP**: Update governance and provide one working example (CompilerPass).
- **Incremental**: Future refactoring of other components will follow this established pattern.

## Dependencies

1. **[US1] Interface Governance**
   - Depends on: T001 (Governance Rule)
