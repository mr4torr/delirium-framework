# Refactoring HTTP Router for Code Quality

## Context
The codebase currently suffers from SRP (Single Responsibility Principle) violations in two key areas:
1. **Core Bootstrapping**: `AppFactory` is a "God Class" that handles module scanning, container construction, and application assembly all in one place.
2. **HTTP Routing**: `RegexDispatcher` handles route matching, argument resolution, and controller invocation in a single monolithic flow.
These issues make the system fragile and difficult to test.

## Goals
- **Refactor `AppFactory`**: Decompose the core bootstrapping logic.
- **Implement `ModuleScanner`**: Extract recursive module discovery logic.
- **Implement `ContainerFactory`**: Extract container configuration and caching logic.
- **Decompose `RegexDispatcher`**: Split the monolithic router class.
- **Implement `Matcher` & `Invoker`**: Dedicated components for routing phases.
- **Improve Testability**: Enable unit testing for individual components (Scanner, Matcher, Invoker).

## Proposed Architecture
> **Clarification - Architecture Components:**
> Based on implementation analysis, the architecture consists of updates to both **Core** and **HTTP Router**:

### 1. Core Refactoring (AppFactory)

#### A. Module Scanner: `Delirium\Core\Module\ModuleScanner`
- **Role**: Discovers module metadata recursively starting from a root module.
- **Behavior**: Scans attributes (e.g., `#[Module]`) and builds a graph of imports, controllers, and providers.
- **Output**: Returns a collected list of service definitions/classes.

#### B. Container Factory: `Delirium\Core\Container\ContainerFactory`
- **Role**: Configures and compiles the Dependency Injection Container.
- **Behavior**:
  - Accepts module metadata.
  - Configures `ContainerBuilder`.
  - Handles container caching (dumping/loading compiled container).
- **Output**: Returns a fully instantiated `Psr\Container\ContainerInterface`.

#### C. Application Factory: `Delirium\Core\AppFactory` (Simplified)
- **Role**: High-level coordinator only.
- **Flow**:
  1. `Scanner->scan(rootModule)`
  2. `ContainerFactory->create(scanResult)`
  3. `new Application(container)`

### 2. Router Refactoring (HTTP)

#### A. Matcher: `Delirium\Http\Routing\Matcher\RegexRouteMatcher`
- **Role**: Matches incoming `ServerRequestInterface` against registered routes.
- **Interface**: `Delirium\Http\Routing\Matcher\RouteMatcherInterface`
  - `add(string $method, string $path, mixed $handler): void`
  - `match(ServerRequestInterface $request): RouteMatch`
- **Behavior**:
  - Supports **Static Routes** (exact string match) for O(1) lookup.
  - Supports **Dynamic Routes** using regex (converts `{param}` to named groups `(?P<param>[^/]+)`).
  - **Exceptions**:
    - Throws `MethodNotAllowedException` if path matches but method differs (includes `Allowed` header info).
    - Throws `RouteNotFoundException` if no match found.
- **Output**: Returns `Delirium\Http\Routing\Matcher\RouteMatch` DTO containing the `handler` and extracted `params`.

#### B. Invoker: `Delirium\Http\Invoker\ControllerInvoker`
- **Role**: Executes the matched handler and manages the request/response lifecycle.
- **Dependencies**:
  - `Psr\Container\ContainerInterface` (optional, for controller instantiation).
  - `Delirium\Http\Resolver\ArgumentResolverChain` (for resolving controller method arguments).
  - `Delirium\Http\Resolver\Response\ResponseResolverChain` (for converting return values to Responses).
- **Capabilities**:
  - logic to instantiation controllers from Container or `new`.
  - Supports `[Class, Method]` array handlers and `Closure` handlers.
  - **Argument Resolution**: Automatically configures a default chain with:
    - `ServerRequestResolver`
    - `RouteParameterResolver`
    - `ContainerServiceResolver` (if container is present)
    - `DefaultValueResolver`
  - **Attribute Extraction**: extracting route configuration (e.g., `type`, `status`) from controller method attributes using reflection. Currently detects generic attributes with `type` or `status` properties.

#### C. Dispatching Coordinator: `Delirium\Http\Router\Dispatcher\RegexDispatcher`
- **Role**: High-level entry point that coordinates the `Matcher` and `Invoker`.
- **Flow**:
  1. Calls `Matcher->match($request)`.
  2. Passes the result to `Invoker->invoke($match->handler, $match->params, $request)`.

## Current State (Analysis)
- Work has begun on `packages/http-router/src/Dispatcher/RegexDispatcher.php` to delegate duties.
- New components appearing in `packages/http-router/src/Invoker/` and `packages/http-router/src/Routing/`.
