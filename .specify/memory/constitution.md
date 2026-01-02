<!--
SYNC IMPACT REPORT
Version: 1.3.0 (Minor)
Date: 2026-01-02

Changes:
- Bumped version to 1.3.0 (Minor) due to introduction of Code Quality Standards principle.
- Added [PRINCIPLE_8] "Code Quality Standards" enforcing SOLID, DRY, and Object Calisthenics.
- Updated Governance dates.

Templates Status:
- .specify/templates/plan-template.md: ✅ Verified
- .specify/templates/spec-template.md: ✅ Verified (No Change Required)
- .specify/templates/tasks-template.md: ✅ Verified

TODOs:
- None.
-->

# Delirium Framework Constitution


## Core Principles

### I. Swoole-First & Async Native

Delirium Framework is specifically built **for** Swoole. We embrace the event-driven, non-blocking I/O nature of the runtime.
- **Async by Default:** All I/O operations (database, network, file system) must use coroutine-friendly libraries. Blocking code in the main reactor loop is strictly prohibited.
- **Long-Running Process:** The application boots once. Static properties and global states persist across requests. Developers must unlearn "request lifecycle = process lifecycle".
- **Coroutine Context:** Request-scoped data must be isolated using `Swoole\Context` to prevent data leaks between concurrent requests.

### II. Design Patterns Driven
Architecture is not reinvented; it is assembled from proven patterns. Every component design must be justifiable via a standard Design Pattern (reference: [Refactoring Guru](https://refactoring.guru/design-patterns)).
- **Intent Matters:** Use patterns to solve the specific problems they were designed for, not just for structure's sake.
- **Common Language:** Use standard pattern names in classes and documentation (e.g., `RequestFactory`, `UserObserver`, `AuthAdapter`).
- **Visibility Order:** Create functions in classes according to their visibility order: first `public`, then `protected`, and finally `private`.
- **Restrictive Functions:** Make the functions as restrictive as possible; by default, functions should have `private` visibility. Use `protected` for functions that apply polymorphism and `public` only for functions that are used externally.
- **Interfaces First:** When necessary, create interfaces or abstract classes to be injected into other classes and functions.
- **Avoid using `mixed`:** Use `mixed` only when it is not possible to use a more specific type.
- **Type Safety:** Classes should be strongly typed, so properties that are of type `array` or `mixed` should have their type applied via the psalm docblock.
- **Naming Conventions:** Functions should use CamelCase naming convention, and classes should use StudlyCase.

### III. Stateless & Memory Safe
Due to the persistent memory model of Swoole:
- **Stateless Services:** Services should be immutable logic containers where possible.
- **Strict Cleanup:** Any resources allocated explicitly (outside standard GC reach) must be released.
- **No Memory Leaks:** Avoid circular references and unbounded growth in static arrays.

### IV. Strict Contracts & Typing
Robustness is achieved through strict interfaces using PHP 8.4+ features.
- **Strict Types:** `declare(strict_types=1);` is mandatory in every file.
- **Interfaces First:** Components communicate via interfaces (`Contracts`), not concrete implementations, enabling easier mocking and swapping.

### V. Modular Architecture

The application is structured as a graph of Modules, inspired by NestJS.
- **Modules as Boundaries:** Every feature MUST be encapsulated in a `Module`. A Module groups related Controllers, Providers (Services), and exports.
- **Dependency Injection:** Dependencies are managed per-module and assembled into the global container.
- **Core vs App:** The Core provides the infrastructure; the Application is composed of Modules.

### VI. Attribute-Driven Meta-Programming
We leverage PHP 8 Attributes to keep configuration close to code, reducing boilerplate files.
- **Declarative Metadata:** Use Attributes for Routing (`@Get`, `@Post`), Dependency Injection (`@Inject`), and Validation.
- **Reflection:** The framework reads these attributes at boot time to build the execution graph.

### VII. Mandatory Testing Discipline
Quality is not an afterthought; it is intrinsic to the creation process.
- **Test-on-Creation:** Every new class, method, or file added to the codebase MUST be accompanied by a corresponding Unit or Integration Test.
- **No Untested Code:** Pull Requests or Commits introducing new features without tests are constitutionally invalid.
- **Coverage:** Tests must verify the success path, failure paths, and edge cases.

### VIII. Code Quality Standards
Code generation and implementation must adhere to recognized craftsmanship standards to ensure long-term maintainability.
- **SOLID Principles:** All code must respect Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, and Dependency Inversion.
- **DRY (Don't Repeat Yourself):** Abstractions should be created to avoid code duplication, provided they do not introduce accidental coupling.
- **Object Calisthenics:** Follow best practices for object-oriented design (e.g., keep methods small, minimize indentation levels, avoid getter/setter abuse where rich domain models are possible).
- **Refactoring Guru:** Design implementation must align with the canonical examples and structures provided by [Refactoring Guru](https://refactoring.guru/design-patterns).

## Design Patterns Architecture


We enforce specific patterns for specific layers to maintain consistency.

### Creational Patterns
*Used to abstract the instantiation process, crucial for dependency injection and driver management.*

**Singleton:**
    *Usage:* STRICTLY LIMITED. Used for the `Application` (Container) instance and Global Managers that must exist once (e.g., `CoroutineManager`).
    *Warning:* Never use for request-scoped objects.
**Factory Method:**
    *Usage:* Driver creation mechanics (e.g., `LoggerFactory::create('json')`, `CacheFactory::create('redis')`).
**Builder:**
    *Usage:* Constructing complex objects with many optional parameters, such as `DbQueryBuilder` or `ResponseBuilder`.

### Structural Patterns
*Used to assemble objects and classes into larger structures while keeping them flexible.*

**Module:**
    *Usage:* The fundamental organizational unit. A class annotated with `@Module` that defines `imports`, `controllers`, `providers`, and `exports`.
**Adapter:**
    *Usage:* Integrating third-party synchronous libraries or wrapping native Swoole clients into PSR-compliant interfaces (e.g., `SwooleReqAdapter` -> `Psr7Request`).
**Decorator:**
    *Usage:* Dynamically adding behavior to objects. Middleware are effectively decorators for the Request Handler. Also used for wrapping Streams.
**Facade:**
    *Usage:* Providing a static proxy to services in the container for DX (Developer Experience), *provided* they proxy to the correct coroutine context.
**Proxy:**
    *Usage:* Lazy loading expensive services. The service is only instantiated when a method is actually called.

### Behavioral Patterns
*Used for effective communication and assignment of responsibilities between objects.*

**Chain of Responsibility:**
    *Usage:* The HTTP Middleware Pipeline. Requests pass through a chain of handlers where each can process or terminate the request.
**Command:**
    *Usage:* CLI Commands and Job Queue tasks. Encapsulates a request as an object.
**Observer:**
    *Usage:* System Events (e.g., `AppBooted`, `RequestReceived`). Allows loosely coupled listeners to react to system state changes.
**Strategy:**
    *Usage:* Swappable algorithms at runtime. Example: Serialization strategies (`JsonStrategy`, `XmlStrategy`) or Auth drivers (`JwtStrategy`, `SessionStrategy`).

## Development Standards

### PSR Compliance
Delirium Framework strictly follows PHP Standard Recommendations (PSRs) to ensure interoperability:
- **PSR-12:** Coding Style.
- **PSR-4:** Autoloading.
- **PSR-7/15/17:** HTTP Message, Handlers, and Factories.
- **PSR-11:** Container Interface.
- **PSR-3:** Logger Interface.
- **PSR-14:** Event Dispatcher.

### Testing Strategy
- **Unit Tests:** Must test logic in isolation (mocking I/O).
- **Integration Tests:** Must run inside a Swoole environment to verify coroutine compatibility and memory behavior.
- **Enforcement:** See Principle VII.

## Governance
This Constitution is the highest law of the Delirium Framework development.
- **Amendments:** Must be proposed via valid PRs and require approval from core maintainers.
- **Enforcement:** Code reviews must explicitly verify adherence to "Swoole-First" and "Design Patterns" principals. Non-compliant code (e.g., blocking I/O) will be rejected immediately.
- **Communication:** Whenever you communicate in the chat, please communicate in Portuguese (Brazil).

**Version:** 1.3.0 | **Ratified:** 2025-12-20 | **Last Amended:** 2026-01-02
