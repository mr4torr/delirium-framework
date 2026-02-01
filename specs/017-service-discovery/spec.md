# Feature Specification: Service Providers & Aliases

**Feature Branch**: `017-service-discovery`
**Created**: 2026-02-01
**Status**: Draft
**Input**: User description: "Implementar as funcionalidades de Class Aliases e Autoloaded Service Providers"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Configuration-Based Provider Loading (Priority: P1)

As a developer, I want to define which Service Providers to load programmatically via the Application instance so that I can easily register package functionality without modifying core kernel code.

**Why this priority**: Essential for modularity and package decoupling logic initiated in feature 016.

**Independent Test**: Identify a new Service Provider, call `$app->register(MyProvider::class)`, launch the app, and verify the service is registered.

**Acceptance Scenarios**:

1. **Given** an Application instance, **When** `$app->register(Provider::class)` is called, **Then** the provider is instantiated and registered.
2. **Given** an environment mismatch (e.g., prod vs dev), **When** `$app->register(Provider::class, 'dev')` is called in `prod`, **Then** the provider is NOT loaded.
3. **Given** the core Kernel hardcodes `DevTools`, **Using** this system, **Then** `DevTools` is loaded via `$app->register(...)` in the factory/bootstrap phase.
4. **Given** a cached configuration exists, **When** the application boots, **Then** it loads providers from cache instead of re-processing registration logic (Optimization).

---

### User Story 2 - Class Aliases (Facades) (Priority: P2)

As a developer, I want to define short class aliases (e.g. `Route`) via the Application instance so that I can use concise syntax in my application code.

**Why this priority**: Improves developer experience (DX) and cleans up application code.

**Independent Test**: Call `$app->alias('Foo', FooService::class)`, and use `Foo::method()` in code.

**Acceptance Scenarios**:

1. **Given** `$app->alias('Short', Full::class)`, **When** the application runs, **Then** using `Short` autoloads `Full`.
2. **Given** repeated alias definitions, **Then** the system handles precedence (last wins).

---

### Edge Cases

- What happens if a provider depends on another provider? (Standard Container logic applies - providers should be order-independent)
- What happens if an alias conflicts with an existing class? (PHP strictness applies - `class_alias` will fail if target already exists)
- Handling of non-existent classes in registration: System MUST throw `\InvalidArgumentException` with message "Provider class [ClassName] does not exist" or "Alias target class [ClassName] does not exist"

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a method (e.g. `register()`) on `Application` to add Service Providers.
- **FR-002**: System MUST provide a method (e.g. `alias()`) on `Application` to register class aliases.
- **FR-003**: Application bootstrapping MUST process these registrations before server start (`listen()`).
- **FR-004**: The `packets/core/Console/Kernel.php` hardcoded logic for DevTools MUST be refactored to use this system.
- **FR-005**: Registration methods MUST support environment-specific loading (e.g. `$app->register(Provider::class, 'dev')`).
- **FR-006**: System MUST implement caching for the consolidated list of providers and aliases (similar to DI container cache) to ensure negligible performance impact on boot.
- **FR-007**: System MUST validate that provider and alias classes exist before registration, throwing a clear exception with the class name if not found.

### Key Entities

- **Application**: The entry point for configuration.
- **ServiceProvider**: A class responsible for binding services.
- **AliasLoader**: Component managing `class_alias` logic.
- **ConfigurationCache**: File storing the resolved list of providers/aliases for fast boot.

## Clarifications

### Session 2026-02-01
- Q: How should environment-specific providers be defined? → A: Use environment keys (`all`, `dev`, `prod`) in the config array structure.
- Q: Should we use config files or code methods? → A: Application codes methods (`register`, `alias`) to set state, processed at boot.
- Q: Performance strategy? → A: Cache the resolved configuration (providers/aliases) to disk to avoid re-calculating logic on every request/boot.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developers can register a new package provider by adding 1 line to a config file.
- **SC-002**: Existing `classes` with long namespaces can be accessed via short aliases.
- **SC-003**: Zero hardcoded package references in `Core\Kernel`.
- **SC-004**: Application boot time impact is negligible (<5ms overhead on cold boot; negligible on warm boot).
