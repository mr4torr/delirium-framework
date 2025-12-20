# Data Model: Dependency Injection

## Entities

### `Delirium\DI\ContainerBuilder`
- **Responsibility**: Bootstraps the Symfony Container, registers compiler passes, scans modules **and their controllers' dependencies** for implicit registration, and dumps the compiled container.
- **Dependencies**: `Symfony\Component\DependencyInjection\ContainerBuilder`, `Symfony\Component\DependencyInjection\Dumper\PhpDumper`.
- **Methods**:
    - `build(string $environment): ContainerInterface`
    - `dump(string $path): void`

### `Delirium\DI\Attribute\Inject`
- **Responsibility**: Attribute to mark properties for injection.
- **Targets**: `TARGET_PROPERTY`
- **Properties**:
    - `?string $serviceId`: Optional explicit service ID.

### `Delirium\DI\Compiler\PropertyInjectionPass`
- **Responsibility**: Scans all definitions in the container for properties with `#[Inject]`, and adds `MethodCall` (setter) or direct property injection logic during compilation.
- **Interface**: `Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface`.

### `Delirium\Core\Container\Container` (Refactor)
- **Responsibility**: This class might become a proxy or a simpler wrapper around the *compiled* container `ProjectServiceContainer`, or we might just use the compiled container directly as the Application's container.
- **Decision**: The Application will likely hold an instance of `Psr\Container\ContainerInterface` which *is* the compiled Symfony container.

## Data Flow

1. **Boot**: `AppFactory::create()` calls `Delirium\DI\ContainerBuilder::build()`.
    - Check if `var/cache/container.php` exists compared to logic rules (dev vs prod).
    - If valid, `require` it and return `new \ProjectServiceContainer()`.
    - If invalid/missing:
        1. Create `Symfony\ContainerBuilder`.
        2. Scan `[Module]` attributes.
        3. Register Services (Controllers, Providers) as Definitions.
        4. Register `PropertyInjectionPass`.
        5. `compile()`.
        6. `dump()` to file.
        7. Return `new \ProjectServiceContainer()`.

2. **Resolution**: `Application` calls `$container->get(Controller::class)`.
    - Compiled container returns fully instantiated Controller with Constructor and Property dependencies injected.
