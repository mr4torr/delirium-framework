# Research: Dependency Injection Strategy

## Decisions

### 1. Underlying Implementation: `symfony/dependency-injection`
- **Decision**: Use `symfony/dependency-injection` as the core container implementation.
- **Rationale**: It is robust, PSR-11 compliant, and provides extensive features like Autowiring and Compilation (Dumper) out of the box. It aligns with the user's suggestion.
- **Alternatives**:
    - `php-di/php-di`: Excellent attribute support but caching mechanism is different. Symfony's dumper is more established for "compile-to-file" optimization.
    - Custom Implementation: Too complex to build from scratch given the "No need to build from zero" directive.

### 2. Property Injection Strategy
- **Decision**: Support `#[Inject]` attribute on properties (public, protected, private).
- **Implementation**:
    - For **private/protected** properties (requested in spec), we will need a **Compiler Pass** or a **Loader** that uses Reflection to find `#[Inject]` attributes and configures the service definition to inject these properties (using `setProperty` or closure-based injection in the dumped container).
- **Rationale**: Meets the user requirement for `#[Inject]` attribute and visibility flexibility.

### 3. Caching Strategy
- **Decision**: Use `Symfony\Component\DependencyInjection\Dumper\PhpDumper`.
- **Implementation**:
    - In `Production` mode: Check for cached container file (`var/cache/dependency-injection.php`). If exists, load it. If not, build, dump, and load.
    - In `Development` mode: Always rebuild (or check timestamp).
- **Rationale**: Directly addresses the user's performance concern by "pre-compiling dependency definitions to a file".

### 4. Implicit Registration (Discovery) Strategy
- **Decision**: Implement a **Dependency Discovery Pass** (Compiler Pass) or extending the `Module` scanner.
- **Implementation**:
    1.  When scanning a `#[Module]`, register its Controllers.
    2.  Iterate over Controller constructors and action methods (methods with Route attributes).
    3.  Extract type-hints (classes/interfaces).
    4.  If a type-hinted class is **not** already registered in the ContainerBuilder, register it automatically as a shared service (autowired).
    5.  Repeat recursively? (Maybe limit to depth 1 first, or rely on Symfony's autowiring to complain if *that* service has missing dependencies, or recursively scan). *Decision: Recursive scanning is safer for Developer Experience.*
- **Rationale**: Fulfills User Story 4 ("use a service... without explicitly registering it").


## Technical Unknowns Resolved

- **Attribute Support**: Symfony supports standard attributes. We can map `#[Inject]` to Symfony's injection logic.
- **Performance**: The `PhpDumper` generates an optimized PHP class, removing reflection overhead at runtime.

## Updated Requirements

- Need to install `symfony/dependency-injection`.
- Need to implement a `Delirium\Core\DI\ContainerBuilder` wrapper that handles the dump/load logic.
