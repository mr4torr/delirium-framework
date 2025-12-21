# Implementation Plan - Map Request Payload (Feature 006)

This plan outlines the implementation of automatic request payload mapping to DTOs/Entities within Controllers, including a new Validation package.

## Technical Context

**Current State**:
- `Delirium\Http\Dispatcher\RegexDispatcher::invokeWithReflection` contains hardcoded logic for resolving controller arguments (Request -> RouteParams -> Container -> Default).
- No validation library exists.
- No hydration mechanism exists.

**Proposed Changes**:
1.  **Refactor Dispatcher**: Extract argument resolution into a Chain of Responsibility (`ArgumentResolverInterface`).
2.  **New Package**: `packages/validation` for constraint validation.
3.  **New Feature**: `MapRequestPayload` attribute and its resolver in `packages/http-router` (or `packages/core` if widely used, but likely `http-router` since it depends on Request).

**Unknowns & Clarifications**:
- [x] **Controller Invocation**: Confirmed in `RegexDispatcher`.
- [x] **Dependency Structure**: `http-router` will depend on `validation`.

## Constitution Check

| Principle | Status | Notes |
| :--- | :--- | :--- |
| **I. Swoole-First** | ✅ | Validation/Hydration is CPU-bound, non-blocking. Safe. |
| **II. Design Patterns** | ✅ | **Chain of Responsibility** for Argument Resolvers. **Attribute-Driven Meta-Programming** for mapping. |
| **III. Stateless** | ✅ | Validators and Hydrators should be stateless services. |
| **IV. Strict Contracts** | ✅ | `ValidatorInterface`, `ArgumentResolverInterface`. |
| **V. Modular Architecture** | ✅ | New `packages/validation`. Logic encapsulated in Resolvers. |
| **VI. Attributes** | ✅ | Heavily used for `#[MapRequestPayload]` and `#[Assert\...]`. |

## Gates

- [ ] **R-Gate (Requirements)**: Spec is approved.
- [ ] **A-Gate (Architecture)**: Plan verifies Constitution alignment.

---

## Phase 0: Research & Design

### Decisions
1.  **Resolver Architecture**: Use a `ResolverChain` in `RegexDispatcher`.
    - `RequestResolver`
    - `RouteParamResolver`
    - `ServiceResolver`
    - `PayloadResolver` (New)
    - `DefaultValueResolver`
2.  **Hydration**: Simple `Reflection` based hydrator initially (as per spec requirements).
3.  **Validation**: Use `symfony/validator`.
    - **Integration**: Create `packages/validation` as an adapter/wrapper (if needed for interface abstraction) or use directly if it complies with our strict types. *Decision: Wrap in `packages/validation` to enforce our own strict `ValidatorInterface` and decouple from Symfony specific exceptions in our Core logic, but expose Symfony Attributes.*

### Artifacts to Generate
- `specs/006-map-request-payload/research.md`
- `specs/006-map-request-payload/data-model.md`

## Phase 1: Validation Package (Adapter)

**Goal**: Integrate `symfony/validator` into `packages/validation`.

- **Create Package**: `packages/validation`.
- **Dependencies**: `composer require symfony/validator`.
- **Interfaces**: `ValidatorInterface` (Our contract).
- **Implementation**: `SymfonyValidatorAdapter` that implements `ValidatorInterface` and delegates to `Symfony\Component\Validator\Validator\ValidatorInterface`.
- **Attributes**: Re-export or document usage of `Symfony\Component\Validator\Constraints` as `Assert`.

## Phase 2: Refactoring Core/Router (Argument Resolvers)

**Goal**: Make argument resolution extensible in Router.

- **Interface**: `Delirium\Http\Contract\ArgumentResolverInterface` in `packages/http-router`.
- **Refactor**: Modify `RegexDispatcher` to use `ArgumentResolverChain`.
- **Built-in Resolvers** (in `packages/http-router`):
    - `ServerRequestResolver`
    - `RouteParameterResolver`
    - `DefaultValueResolver`
- Note: `ContainerServiceResolver` might need to be in `Core` if Router shouldn't depend on generic Container, but Router usually has ContainerAwareness. We'll keep basic Container resolution in Router for now, or move to Core if strict. *Decision: Keep basic Container/Service resolution in Router as it already depends on ContainerInterface.*

## Phase 3: Map Request Payload Implementation (Core Integration)

**Goal**: Implement the mapping logic in `packages/core`.

- **Hydrator**: `Delirium\Core\Hydrator\ObjectHydrator` (In Core).
- **Attribute**: `Delirium\Http\Attribute\MapRequestPayload` (In Router, so Controllers can use it).
- **Resolver**: `Delirium\Core\Resolver\PayloadResolver` (In Core).
    - Implements `Delirium\Http\Contract\ArgumentResolverInterface`.
    - Depends on `Delirium\Validation\Contract\ValidatorInterface`.
    - Logic:
        1. Check for `MapRequestPayload` attribute.
        2. Decode Body.
        3. Hydrate.
        4. Validate.
- **Wiring**: `Application::configureServer` or `AppFactory` must register `PayloadResolver` into the `Router`'s dispatcher.

## Verification Plan

### Automated Tests
- **Unit Tests (Validation)**: Test constraints independently.
- **Unit Tests (Hydrator)**: Test population of objects (public props, constructor).
- **Integration Tests (Router)**:
    - Test existing functionalities (Routes, DI) to ensure no regression functionality after refactor.
    - Test `MapRequestPayload` with valid JSON.
    - Test `MapRequestPayload` with invalid JSON type (Best Effort check).
    - Test `MapRequestPayload` with Validation Errors (422 check).
    - Test Mixed Arguments (DI + Route + Payload).

### Manual Verification
- Create a `UserController` with `create(#[MapRequestPayload] CreateUserDto $dto)`.
- Use `curl` to send valid/invalid requests.
