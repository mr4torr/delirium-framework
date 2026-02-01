# Research: Service Discovery & Performance

## Decisions

### 1. `class_alias` in Swoole
- **Decision**: Use native `class_alias()`.
- **Rationale**: In Swoole, the application boots once in the Master/Manager process before forking Workers (or at the start of Workers depending on the configuration). Class aliases are global to the PHP process. Since the aliases are meant to be framework-wide shortcuts (e.g., `Route` for `Delirium\Http\Router`), global scope is exactly what we need.
- **Coroutine Safety**: `class_alias` does not interact with the coroutine scheduler or stack; it modifies the class lookup table. It is safe for long-running processes as long as we don't try to redefine aliases per-request (which we won't).

### 2. Registration Caching Strategy
- **Decision**: Semi-Automatic Cache Invalidation.
- **Rationale**: Unlike the DI container which can be automatically recompiled when configuration files change (using file-watchers), programmatic calls to `$app->register()` are dynamic.
- **Implementation**:
    - **Development**: Caching is DISABLED by default or uses a checksum of the files where registration happens (if detectable). Alternatively, always rebuild.
    - **Production**: Developers run a command (e.g., `bin/console app:optimize` or `composer dump-autoload`) that triggers a "Pre-boot" scan or we perform the first boot and save the state.
    - **MVP Approach**: The `ProviderRepository` will check for the existence of `var/cache/discovery.php`. If it doesn't exist, it collects registrations. In `listen()`, if not cached, it writes the manifest.

### 3. Service Provider Lifecycle
- **Decision**: Two-step boot (`register` -> `boot`).
- **Rationale**:
    - `register()`: Only for binding things into the container. Should NEVER resolve other services.
    - `boot()`: Called after ALL providers are registered. Safe to resolve services and perform initialization logic.
- **Reference**: This follows the well-established pattern used by Laravel and Symfony (via Bundles).

### 4. Class Validation Strategy (Edge Case E1)
- **Decision**: Eager validation with clear exceptions.
- **Rationale**:
    - Failing fast during registration (not at runtime) provides better developer experience.
    - Clear exception messages help developers identify typos or missing dependencies immediately.
- **Implementation**:
    - `Application::register()` will use `class_exists($provider, true)` before adding to repository.
    - `Application::alias()` will use `class_exists($class, true)` before adding to loader.
    - Both throw `\InvalidArgumentException` with descriptive messages including the class name.

## Technical Unknowns Resolved

- **Performance**: Reading a flat PHP array from `var/cache/discovery.php` is ~10-20x faster than instantiating reflection classes or iterating dynamically on every boot.
- **Namespace Conflicts**: The `AliasLoader` will throw a LogicException if a developer tries to alias a name that is already a "real" class or is already aliased to something else.
- **Validation Overhead**: `class_exists()` with autoload enabled has negligible overhead (<0.1ms per check) and prevents cryptic errors later.

## Alternatives Considered

- **Autodiscovery via `composer.json`**: Good for zero-config, but the user explicitly asked for functions on `Application.php`. We will stick to the programmatic approach as it gives the developer full control.
- **Lazy Validation**: Validate only when instantiating providers - rejected because it delays error detection and makes debugging harder.
