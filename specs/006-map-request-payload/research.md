# Research: Map Request Payload

**Feature**: Map Request Payload (006)

## Decisions

### 1. Argument Resolution Strategy
**Decision**: Chain of Responsibility (`ArgumentResolverChain`).
**Rationale**:
- **Extensibility**: Allows adding new resolvers (like PayloadResolver) without modifying the Dispatcher core logic indefinitely.
- **Separation of Concerns**: Each resolver handles one type of argument (Request, Route Param, Service, Payload).
- **Testability**: Resolvers can be tested in isolation.

**Alternatives Considered**:
- **Hardcoding in Dispatcher**: Current implementation. Rejected because it violates OCP (Open/Closed Principle) and makes the Dispatcher class huge and complex.

### 2. Package Independence (Modular Architecture)
**Decision**: `PayloadResolver` in `packages/core`.
**Rationale**:
- **Strict Decoupling**: User rules state strict independence for packages (except Core). `http-router` cannot depend on `validation`.
- **Core as Glue**: Core is the allowed place for cross-package integration. It will depend on both `http-router` (interface) and `validation` (implementation/interface) to orchestrate mapping.

### 3. Validation Architecture
**Decision**: Use `packages/validation` as an Adapter for `symfony/validator`.
**Rationale**:
- **Reliability**: Uses a battle-tested library (`symfony/validator`) instead of reinventing the wheel.
- **Standards**: Leverages standard constraints that developers are already familiar with.
- **Abstraction**: Our `ValidatorInterface` decouples the application from the specific underlying library version, adhering to strict contracts.
- **Decoupling**: Controller/Router doesn't need to know *how* validation works, just that it needs to validate.

### 3. Hydration Strategy
**Decision**: Reflection-based `ObjectHydrator`.
**Rationale**:
- **Simplicity**: No need for code generation or complex mapping config for V1.
- **Compatibility**: Supports PHP 8.4 Constructor Promotion out of the box.

### 4. Integration Point
**Decision**: `PayloadResolver` executes Hydration AND Validation.
**Rationale**:
- **Fail Fast**: If data is invalid, don't execute the controller.
- **Developer Experience**: user gets a ready-to-use, valid object.
