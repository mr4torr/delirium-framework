# Tasks: PSR-7 Support

**Feature**: `013-psr7-support` | **Status**: Pending

## Phase 1: Setup
- [x] T001 Create directory structure for Contracts, Messages, Resolvers in `packages/http-router/src/`

## Phase 2: Foundational (Contracts & Adapters)
*Goal: Establish the base PSR-7/17 compatible interfaces and classes required for Dependency Injection.*

- [x] T002 [US1] Define `Delirium\Http\Contract\RequestInterface` in `packages/http-router/src/Contract/RequestInterface.php`
- [x] T003 [US1] Define `Delirium\Http\Contract\ResponseInterface` in `packages/http-router/src/Contract/ResponseInterface.php`
- [x] T004 [US1] Create `Delirium\Http\Message\Request` adapter in `packages/http-router/src/Message/Request.php`
- [x] T005 [US1] Create `Delirium\Http\Message\Response` adapter in `packages/http-router/src/Message/Response.php`
- [x] T006 [US1] Update `AppFactory` to bind interfaces to implementations in `packages/core/src/AppFactory.php`

## Phase 3: Request Helpers (User Story 2)
*Goal: Implement Hyperf-compatible helper methods in the Request object.*

- [x] T007 [P] [US2] Implement `input`, `all`, `query`, `post` methods in `packages/http-router/src/Message/Request.php`
- [x] T008 [P] [US2] Implement `header`, `cookie`, `file`, `has` methods in `packages/http-router/src/Message/Request.php`
- [x] T009 [US2] Create unit tests for Request helpers in `packages/http-router/tests/Message/RequestTest.php`

## Phase 4: Response Helpers (User Story 3)
*Goal: Implement helper methods for response generation (JSON, XML, Redirects, etc.).*

- [x] T010 [P] [US3] Implement `json`, `xml`, `raw` methods in `packages/http-router/src/Message/Response.php`
- [x] T011 [P] [US3] Implement `redirect`, `download`, `withCookie` methods in `packages/http-router/src/Message/Response.php`
- [x] T012 [US3] Create unit tests for Response helpers in `packages/http-router/tests/Message/ResponseTest.php`

## Phase 5: Response Resolution (User Story 4)
*Goal: Implement the Response Resolver Chain to handle declarative route attributes and transform controller returns.*

- [x] T013 [US4] Create `Reflector` based `ResponseResolverChain` logic in `packages/http-router/src/Resolver/Response/ResponseResolverChain.php`
- [x] T014 [P] [US4] Implement `JsonResolver` in `packages/http-router/src/Resolver/Response/JsonResolver.php`
- [x] T015 [P] [US4] Implement `XmlResolver` in `packages/http-router/src/Resolver/Response/XmlResolver.php`
- [x] T016 [P] [US4] Implement `StreamResolver` in `packages/http-router/src/Resolver/Response/StreamResolver.php`
- [x] T017 [US4] Implement `HtmlResolver` in `packages/http-router/src/Resolver/Response/HtmlResolver.php`
- [x] T018 [US4] Update `RegexDispatcher` to fully utilize the chain in `packages/http-router/src/Dispatcher/RegexDispatcher.php`
- [x] T019 [US4] Register new resolvers in `AppFactory` in `packages/core/src/AppFactory.php`
- [x] T020 [US4] Create integration tests for Route Attributes in `packages/http-router/tests/Dispatcher/AttributeResponseTest.php`

## Dependencies

1. **US1 (DI & Contracts)**: Blocks everything. Must be done first.
2. **US2 (Request API)**: Depends on T004.
3. **US3 (Response Helpers)**: Depends on T005.
4. **US4 (Resolution)**: Depends on US3 (needs Response object generation) and US1 contracts.

## Implementation Strategy
- **MVP**: Complete Phase 1 & 2 to ensure the framework boots with the new DI types.
- **Incremental**: Add helpers (Phase 3 & 4) in parallel.
- **Feature Complete**: Finalize with Attribute Resolution (Phase 5).
