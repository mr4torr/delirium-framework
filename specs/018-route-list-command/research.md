# Research: Route List Command

**Feature**: Route List Command (018-route-list-command)
**Status**: APPROVED
**Date**: 2026-02-14

## Problem Statement

We need a way to visualize all registered routes in the application for debugging and development purposes, leveraging the existing `http-router` package and `symfony/console`.

## Decisions

### 1. Command Implementation
**Decision**: Extend `Symfony\Component\Console\Command\Command`.
**Rationale**: The framework already uses `symfony/console` via `delirium/core`. Extending the base command allows seamless integration with the existing Kernel and Console runner.
**Alternatives**:
- *Custom CLI runner*: Rejected. Re-inventing the wheel when `symfony/console` is already a dependency.

### 2. Route Retrieval
**Decision**: Inject `Delirium\Http\Contract\RouterInterface` (or `RouteRegistry`).
**Rationale**: `RouteRegistry` is the single source of truth for routes. The `Router` exposes this registry.
**Context**: `Router::getRegistry()->getRoutes()` returns `[method => [path => handler]]`.

### 3. Registration Mechanism
**Decision**: Use a dedicated `HttpRouterServiceProvider`.
**Rationale**: Keeping registration logic within the package causing the side-effect (adding a command) adheres to the Open/Closed Principle. The core `ProviderRepository` will load this provider.
**Alternatives**:
- *Hardcoding in Kernel*: Rejected. Increases coupling between Core and specific packages.

## Implementation Details

- **Output Format**: Table view using `Symfony\Component\Console\Helper\Table`.
- **Columns**: Method, URI, Handler.
- **Handling Closures**: Closures cannot be easily printed. Display "Closure" string or file/line if available via reflection (optional polish).
