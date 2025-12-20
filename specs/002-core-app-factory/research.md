# Research: Core App Factory

## Dependency Injection Strategy
**Decision**: Implement a lightweight PSR-11 Container (`Delirium\Container\Container`).
**Rationale**: The framework lacks a container. To support the requested "NestJS-style" module system with `providers`, a central registry is required.
**Alternatives**: Use `php-di/php-di` or `league/container`.
**Selection**: Build internal simple container to maintain zero-dependencies philosophy (besides PSR interfaces) and ensure tight integration with the `AppModule` scanning logic. External libs can be added later if complexity grows.

## OpenSwoole Integration
**Decision**: `DeliriumFactory::create()` will instantiate `Delirium\Core\Application` which wraps `OpenSwoole\Http\Server`.
**Rationale**: Keeps the core decoupled from the specific server implementation details until boot.
**Configuration**: Port and Host will be passed via `AppOptions` array/object.

## Module Scanning
**Decision**: Depth-First Search (DFS) or Recursive Traversal.
**Rationale**: To support nested modules (e.g., `App -> Public -> Auth`), the scanner must verify attributes on imported classes and traverse their `imports` array before finalizing the graph.
**Complexity**: Must detect and prevent infinite recursion (circular dependencies) by keeping track of visited modules.
