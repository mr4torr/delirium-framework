# Implementation Plan: Service Providers & Aliases

**Branch**: `017-service-discovery` | **Date**: 2026-02-01 | **Spec**: [specs/017-service-discovery/spec.md]
**Input**: Feature specification from `/specs/017-service-discovery/spec.md`

## Summary

Implement a dynamic service discovery mechanism using **Service Providers** and **Class Aliases**. This allows packages to self-register into the DI container and provides a "Facade"-like experience for developers using alias shortcards. The implementation will favor programmatic registration on the `Application` instance while utilizing a file-based caching manifest for the resolved discovery graph to ensure high performance in consistent environments.

## Technical Context

**Language/Version**: PHP 8.4+ (utilizing strict types and possibly property hooks if applicable)
**Primary Dependencies**: `delirium/support`, `delirium/core`, `psr/container`
**Storage**: File-based cache in `var/cache/discovery.php`
**Testing**: `phpunit` (Unit and Integration)
**Target Platform**: Swoole / Linux
**Project Type**: Monorepo packages (`core`, `support`, `dev-tools`)
**Performance Goals**: <5ms discovery overhead on cold boot; negligible on warm boot.
**Constraints**: Must adhere to Swoole's long-running process model (state persists).
**Scale/Scope**: Support for 100+ providers and 500+ aliases.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **Mandatory Testing**: Confirm that every planned new class/file has a corresponding test task. (Will ensure `ProviderRepositoryTest` and `AliasLoaderTest` are created).
- [x] **Code Quality**: Adheres to SOLID/DRY. Uses **Registry** (ProviderRepository) and **Facade** (via AliasLoader) patterns.
- [x] **Swoole-First**: Registration happens at boot time (once per process).

## Project Structure

### Documentation (this feature)

```text
specs/017-service-discovery/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
└── tasks.md             # (Created by /speckit.tasks)
```

### Source Code (repository root)

```text
packages/
├── support/
│   └── src/
│       └── ServiceProvider.php      # Abstract base for all providers
├── core/
│   └── src/
│       ├── Foundation/
│       │   ├── ProviderRepository.php # Manages registration/load sequence + caching
│       │   └── AliasLoader.php        # Manages class_alias registration
│       └── Application.php          # updated with register() and alias()
└── dev-tools/
    └── src/
        └── DevToolsServiceProvider.php # Example/Real implementation
```

**Structure Decision**: Modular Application. We extend the `core` foundation layer to support these two discovery mechanisms. Contracts live in `support`.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Mandatory Cache | Performance is a core pillar. | Runtime iteration is acceptable for small apps, but the framework must scale to large monorepos. |

## Phase 0: Research

1. **Research class_alias in Swoole**: Confirm there are no isolation issues in coroutine contexts (aliases are global to the PHP process, so this should be fine for framework-level shortcuts).
2. **Research Cache Invalidation**: How to automatically invalidate the discovery cache when `Application::register()` calls change in development mode.

## Phase 1: Design & Contracts

1. **Contracts**: Define `ServiceProvider` with `register()` and `boot()` methods.
2. **Data Model**: Define the structure of the `var/cache/discovery.php` manifest.
3. **Quickstart**: Document how to register a provider and an alias in the `AppFactory`.
