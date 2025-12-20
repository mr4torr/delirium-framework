# Data Model

## Key Entities

### 1. DeliriumFactory
**Responsibility**: Static entry point for application creation.
**Methods**:
- `create(string $moduleClass, array $options = []): Application`

### 2. Application
**Responsibility**: Runtime instance managing the Server and Container.
**Properties**:
- `server`: OpenSwoole Server instance.
- `container`: DI Container instance.
- `router`: Router instance.
**Methods**:
- `listen(int $port = 9501, string $host = '0.0.0.0'): void`
- `get(string $id): mixed` (Proxy to container)

### 3. Container
**Responsibility**: PSR-11 Dependency Injection Container.
**Methods**:
- `set(string $id, mixed $value): void`
- `get(string $id): mixed`
- `has(string $id): bool`

### 4. Application Graph
**Responsibility**: Directed Acyclic Graph (DAG) representing the module hierarchy.
**Nodes**: `AppModule` instances.
**Edges**: `imports` relationships.
**Validation**: Detect cycles during construction.

### 5. Attributes

#### `#[AppModule]`
**Target**: Class
**Properties**:
- `imports` (array of class strings)
- `controllers` (array of class strings)
- `providers` (array of class strings or factory callables)

#### `#[Inject]` (Future Scope, but good to note)
**Target**: Property/Constructor Param
**Purpose**: Explicit injection, though type-hinting usually suffices.

## Relationships

1. `DeliriumFactory` creates `Application`.
2. `DeliriumFactory` initiates `ModuleScanner`.
3. `ModuleScanner` recursively traverses `RootModule` imports to build `Application Graph`.
4. `ModuleScanner` registers `providers` into `Container`.
5. `ModuleScanner` registers `controllers` with `Router`.
6. `Application` holds `Container` and `Router`.
