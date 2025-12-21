# Research: Use PSR Interfaces in Method Injection

**Decision**: Enforce PSR-11 (`Psr\Container\ContainerInterface`) and PSR-7 (`Psr\Http\Message\ServerRequestInterface`) for all user-land injections.

**Rationale**:
- **Decoupling**: Prevents user code from depending on `OpenSwoole` or `Symfony` specific classes.
- **Interoperability**: Allows easier swapping of underlying implementations (e.g., changing HTTP server or DI container).
- **Standards**: Aligns with the framework's "Standards Compliance" pillar.

**Technical Analysis**:
- **Current State**:
  - `PostController` uses `Nyholm\Psr7\Response` (Concrete).
  - `ContainerServiceResolver` supports `ContainerInterface`.
  - `PayloadResolver` supports `ServerRequestInterface`.
  - `ContainerBuilder` aliases `ContainerInterface` to the service container.
- **Gap**:
  - Code examples in `src/` need update.
  - No explicit test for `ServerRequestInterface` injection in controllers.

**Alternatives Considered**:
- **Use `OpenSwoole\Http\Request` directly**:
  - *Pros*: Access to Swoole-specific features (fd, reactor_id).
  - *Cons*: Tight coupling to Swoole. Harder to test with standard tools.
  - *Decision*: Rejected. Users can still type-hint concrete class if they *really* need it (framework allows it), but defaults must be PSR.

- **Use `Symfony\Component\DependencyInjection\ContainerInterface`**:
  - *Cons*: Deprecated/Legacy interface in some contexts, less standard than PSR-11.
  - *Decision*: Rejected in favor of PSR-11.
