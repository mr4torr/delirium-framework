# Implementation Plan: PSR-7 Support

**Branch**: `013-psr7-support` | **Date**: 2026-01-02 | **Spec**: [specs/013-psr7-support/spec.md](specs/013-psr7-support/spec.md)
**Input**: Feature specification from `specs/013-psr7-support/spec.md`

## Summary

Implement full **PSR-7** and **PSR-17** support in the http-router package by creating `Request` and `Response` adapter classes that extend standard `Psr\Http\Message` interfaces with helper methods. Additionally, implement a `ResponseResolverChain` in the `RegexDispatcher` to post-process controller return values (arrays, strings, objects) into valid `ResponseInterface` objects based on declarative Route Attributes (`type`, `status`), replacing the ad-hoc return handling.

## Technical Context

**Language/Version**: PHP 8.4+
**Primary Dependencies**: `nyholm/psr7` (Core), `psr/http-message`, `psr/http-factory` (PSR-17), `psr/container`
**Target Platform**: Linux (Swoole environment)
**Project Type**: Framework Package (`delirium/framework`)
**Performance Goals**: Zero-copy where possible, efficient wrapping of PSR-7 objects.
**Constraints**: strictly use `declare(strict_types=1)`, no static state in Request/Response.

## Constitution Check

- **Swoole-First & Async Native**: ✅ Implementation uses `nyholm/psr7` which is lightweight. No blocking I/O introduced.
- **Design Patterns Driven**: ✅ Uses Adapter (Request/Response wrappers), Chain of Responsibility (Response Resolvers), Strategy (Attribute handling).
- **Stateless & Memory Safe**: ✅ Request/Response objects are created per-request and discarded. No static caches.
- **Strict Contracts & Typing**: ✅ New Interfaces (`Delirium\Http\Contract\RequestInterface`) defined.
- **Modular Architecture**: ✅ Changes confined to `http-router` and `core` wiring.
- **Attribute-Driven Meta-Programming**: ✅ Route Attributes (`type`, `status`) drive response logic.

## Project structure

### Documentation (this feature)

```text
specs/013-psr7-support/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
└── contracts/
```

### Source Code

```text
packages/
├── http-router/
│   ├── src/
│   │   ├── Contract/
│   │   │   ├── RequestInterface.php         # [NEW] Extends Psr\ServerRequestInterface + Helpers
│   │   │   └── ResponseInterface.php        # [NEW] Extends Psr\ResponseInterface + Helpers
│   │   ├── Message/
│   │   │   ├── Request.php                  # [NEW] Adapter for Nyholm\Psr7\ServerRequest
│   │   │   └── Response.php                 # [NEW] Adapter for Nyholm\Psr7\Response
│   │   ├── Resolver/
│   │   │   └── Response/                    # [NEW] Directory for Response Resolvers
│   │   │       ├── ResponseResolverChain.php
│   │   │       ├── JsonResolver.php
│   │   │       ├── XmlResolver.php
│   │   │       ├── HtmlResolver.php
│   │   │       └── StreamResolver.php
│   │   └── Dispatcher/
│   │       └── RegexDispatcher.php          # [MODIFY] Integrate ResponseResolverChain
└── core/
    └── src/
        └── AppFactory.php                   # [MODIFY] Wire new Resolvers
```

## Proposed Changes

### packages/http-router

#### [NEW] [RequestInterface.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Contract/RequestInterface.php)
- Extends `Psr\Http\Message\ServerRequestInterface`.
- Adds methods: `input()`, `header()`, `cookie()`, `query()`, `file()`, `has()`, `all()`.

#### [NEW] [ResponseInterface.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Contract/ResponseInterface.php)
- Extends `Psr\Http\Message\ResponseInterface`.
- Adds methods: `json()`, `xml()`, `redirect()`, `download()`, `withCookie()`, `raw()`.

#### [NEW] [Request.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Message/Request.php)
- Implements `Delirium\Http\Contract\RequestInterface`.
- Composes `Psr\Http\Message\ServerRequestInterface`.
- Implements helper logic (e.g., `input()` priority: parsed body > query params).

#### [NEW] [Response.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Message/Response.php)
- Implements `Delirium\Http\Contract\ResponseInterface`.
- Composes `Psr\Http\Message\ResponseInterface`.
- Uses `Nyholm\Psr7\Factory\Psr17Factory` to create streams/responses internally for helpers.

#### [NEW] [Resolver/Response Directory](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Resolver/Response)
- `ResponseResolverChain.php`: Iterates resolvers to transform mixed return value to `ResponseInterface`.
- `JsonResolver.php`: Handles `type: 'json'` or array returns.
- `XmlResolver.php`: Handles `type: 'xml'`.
- `DefaultValueResolver.php`: Fallback.

#### [MODIFY] [RegexDispatcher.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Dispatcher/RegexDispatcher.php)
- Inject `ResponseResolverChain`.
- In `invokeWithReflection`:
  - Execute controller.
  - Pass result + route attributes to `ResponseResolverChain`.
  - Return final `ResponseInterface`.

### packages/core

#### [MODIFY] [AppFactory.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/core/src/AppFactory.php)
- Wire up the `ResponseResolverChain` with all specific resolvers.
- Configure `RegexDispatcher` with the chain.

## Verification Plan

### Automated Tests
- **Unit Tests**:
  - `RequestTest.php`: Mock PSR-7 request, test helper methods `input`, `header`, etc.
  - `ResponseTest.php`: Test `json`, `redirect` output generation.
  - `ResponseResolverChainTest.php`: Test logic for selecting correct resolver based on attributes.
  - `JsonResolverTest.php`, etc.
- **Integration Tests**:
  - `RouterTest.php`: Verify end-to-end flow:
    - Route definition `#[Get('/', type: 'json')]`.
    - Controller returns array.
    - Assert response is JSON with correct headers.

### Manual Verification
- N/A (Library package, verified via Integration Tests).
