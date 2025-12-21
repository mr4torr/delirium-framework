# Feature 006: Map Request Payload

**Spec**: `specs/006-map-request-payload/spec.md`
**Plan**: `specs/006-map-request-payload/plan.md`

## Phase 1: Setup & Validation Package (Adapter)
*Goal: Initialize new package and integrate validation library.*

- [x] T001 Create `packages/validation` directory and `composer.json`
- [x] T002 Update root `composer.json` to include `Delirium\Validation` namespace
- [x] T003 Install `symfony/validator` in `packages/validation/composer.json`
- [x] T004 Implement `Delirium\Validation\Contract\ValidatorInterface` in `packages/validation/src/Contract/ValidatorInterface.php`
- [x] T005 Implement `SymfonyValidatorAdapter` in `packages/validation/src/Adapter/SymfonyValidatorAdapter.php`
- [x] T006 Create `Delirium\Validation\Attribute\Assert` alias/helper (or documentation point) if needed, otherwise rely on Symfony Constraints directly

## Phase 2: Router Refactoring (Argument Resolvers)
*Goal: Decouple argument resolution from Dispatcher.*

- [x] T007 Define `Delirium\Http\Contract\ArgumentResolverInterface` in `packages/http-router/src/Contract/ArgumentResolverInterface.php`
- [x] T008 Implement `ArgumentResolverChain` in `packages/http-router/src/Resolver/ArgumentResolverChain.php`
- [x] T009 Implement `ServerRequestResolver` in `packages/http-router/src/Resolver/ServerRequestResolver.php`
- [x] T010 Implement `RouteParameterResolver` in `packages/http-router/src/Resolver/RouteParameterResolver.php`
- [x] T011 Implement `DefaultValueResolver` in `packages/http-router/src/Resolver/DefaultValueResolver.php`
- [x] T012 Implement `ContainerServiceResolver` in `packages/http-router/src/Resolver/ContainerServiceResolver.php` (keeping basic DI in router as decided)
- [x] T013 [US3] Refactor `Delirium\Http\Dispatcher\RegexDispatcher` to use `ArgumentResolverChain` instead of hardcoded logic

## Phase 3: Map Request Payload Integration (Core)
*Goal: Implement the mapping logic and wiring.*

- [x] T014 [US1] Create `#[MapRequestPayload]` attribute in `packages/http-router/src/Attribute/MapRequestPayload.php`
- [x] T015 [US1] Implement `Delirium\Core\Hydrator\ObjectHydrator` in `packages/core/src/Hydrator/ObjectHydrator.php` (Reflection based)
- [x] T016 [US1] [US2] [US4] Implement `Delirium\Core\Resolver\PayloadResolver` in `packages/core/src/Resolver/PayloadResolver.php` (Depends on Hydrator + Validator)
- [x] T017 [US1] Register `PayloadResolver` and other resolvers in `Application` or `AppFactory` (Wiring)

## Phase 4: Verification
*Goal: Verify all user stories.*

- [x] T018 [US1] Create `MapRequestPayloadTest` integration test for happy path (JSON -> DTO)
- [x] T019 [US2] Verify Entity Mapping support (same mechanism)
- [x] T020 [US3] Verify Mixed Usage (DI + Route + Payload) in a single controller method
- [x] T021 [US4] Verify Validation failure (422) and Malformed JSON (400) handling
- [x] T022 Manual Verification: Create demo controller and curl requests (Quickstart)

## Dependencies

- Phase 2 depends on Phase 1 (technically independent, but clean architecture)
- Phase 3 depends on Phase 1 & 2
- T017 depends on all previous resolvers
- T018+ depends on T017
