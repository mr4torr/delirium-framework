# Feature Specification: Dependency Injection

**Feature Branch**: `003-dependency-injection`
**Created**: 2025-12-20
**Status**: Draft
**Input**: User description: "Implemente a funcionalidade de injeção de dependência de acordo com o padrão PSR-11, de forma que, ao definir classes no construtor ou nas funções de uma classe do tipo controller, o sistema seja capaz de identificar e realizar a injeção dessas dependências ao acessar o endpoint correspondente. O comportamento desejado deve ser similar ao adotado por frameworks como NestJS, Laravel e Symfony. Não é necessário criar a solução do zero, podendo ser utilizados pacotes existentes, como o symfony/dependency-injection, desde que a implementação siga a interface especificada pelo PSR-11. Classes que foram injetadas e que tambem possuam em seu construtor parametros, tambem devem seguir o mesmo comportamento de injeção."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Constructor Injection (Priority: P1)

As a developer, I want to define dependencies in my Controller's constructor so that they are automatically available to all methods in the class without manual instantiation.

**Why this priority**: It is the most standard way to manage dependencies in robust applications, promoting testability and loose coupling.

**Independent Test**: Create a Controller with a Service dependency in the constructor. Verify that when a route is hit, the Controller is instantiated with the Service, and the Service works as expected.

**Acceptance Scenarios**:

1. **Given** a Controller with a `UserService` type-hinted in its `__construct`, **When** the application receives a request for a route handled by this Controller, **Then** the `UserService` is automatically instantiated and passed to the constructor.
2. **Given** a `UserService` that itself has a `UserRepository` dependency in its `__construct`, **When** the Controller is instantiated, **Then** the `UserRepository` is automatically resolved and injected into `UserService` (Recursive Injection).

---

### User Story 2 - Method Injection (Priority: P2)

As a developer, I want to type-hint dependencies directly in Controller action methods so that I only instantiate heavy services when they are actually needed for that specific endpoint.

**Why this priority**: Provides flexibility and performance optimization for controllers with many actions that require different disjoint sets of dependencies.

**Independent Test**: Create a Controller method that accepts a specific Service as an argument alongside route parameters. Verify the Service is injected correctly.

**Acceptance Scenarios**:

1. **Given** a Controller method `index(ReportService $service)`, **When** the route for `index` is accessed, **Then** an instance of `ReportService` is passed to the method.
2. **Given** a Controller method `find(int $id, UserService $service)`, **When** the route `/users/1` is accessed, **Then** `$id` receives `1` (from route) and `$service` receives a valid `UserService` instance.

---

### User Story 3 - Property Injection (Priority: P2)

As a developer, I want to inject dependencies directly into class properties using an `#[Inject]` attribute so that I can define dependencies without writing boilerplate constructor code.

**Why this priority**: specified by user request; provides a cleaner syntax for certain dependency patterns (like optional dependencies or avoiding massive constructors).

**Independent Test**: Create a generic class or controller with a property annotated with `#[Inject]`. Verify the property is populated after instantiation.

**Acceptance Scenarios**:

1. **Given** a class with a public property `#[Inject] public UserService $userService`, **When** the class is resolved by the container, **Then** `$userService` contains a valid instance of `UserService`.
2. **Given** a protected or private property annotated with `#[Inject]`, **When** the class is resolved, **Then** the system populates it (via reflection) or throws a clear error if visibility is restricted by design (spec assumes reflection is used).

---

### User Story 4 - Implicit Registration (Priority: P2)

As a developer, I want to use a service in my Controller simply by type-hinting it, without explicitly registering it in the Module's `providers` array, so that I can prototype and develop features faster.

**Why this priority**: Enhances Developer Experience (DX) by reducing boilerplate configuration.

**Independent Test**: Create a Controller and a Service. Type-hint the Service in the Controller. Do NOT list the Service in any Module `providers`. Verify it is injected.

**Acceptance Scenarios**:

1. **Given** a Controller `c` that depends on `NewService`, **And** `NewService` is NOT listed in `#[Module(providers: [...])]`, **When** the app boots, **Then** the system detects the dependency and automatically registers `NewService` in the container.
2. **Given** a Method Injection scenario `index(OtherService $s)`, **When** the route is hit, **Then** `OtherService` is resolved and injected even if not explicitly provided.

### Edge Cases

- What happens when a dependency involves a circular reference (A depends on B, B depends on A)?
  - System should throw a clear `ContainerException` or `CircularReferenceException` instead of entering an infinite loop.
- What happens when a dependency is an Interface without a concrete class binding?
  - System should throw a `NotFoundException` or `BindingResolutionException` unless a default value is provided.
- What happens when a primitive type (int, string) is required in a constructor but not bound?
  - System should throw an exception indicating the parameter cannot be resolved.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST implement a Container that complies with `Psr\Container\ContainerInterface`.
- **FR-002**: System MUST support **Auto-wiring**, enabling the resolution of classes based on type-hints without requiring explicit manual configuration for every class.
- **FR-003**: System MUST support **Recursive Resolution**, ensuring that dependencies of dependencies are resolved automatically down the chain.
- **FR-004**: System MUST inject dependencies defined in **Controller Constructors** when routing a request.
- **FR-005**: System MUST inject dependencies defined in **Controller Action Methods** when routing a request, prioritizing them correctly against route parameters.
- **FR-006**: System MUST allow the integration of existing packages (e.g., `symfony/dependency-injection` or `php-di`) as the underlying implementation, provided the interface remains PSR-11 compliant.
- **FR-007**: System MUST inject dependencies into properties annotated with a specific attribute (e.g., `#[Inject]`), resolving the type from the property definition.
- **FR-008**: System MUST support a **Caching Mechanism** that pre-compiles or maps dependency definitions (e.g., to a file) during initialization to minimize runtime reflection overhead.
- **FR-009**: System MUST automatically register classes that are type-hinted in Controller **constructors** or **route action methods** if they are not already explicitly registered.

### Key Entities

- **Container**: The central registry responsible for resolving and holding service instances.
- **Provider**: A class or definition that can be injected as a dependency.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developers can add a new Service class and use it in a Controller constructor without modifying any configuration files (Auto-wiring matches 100% of concrete classes).
- **SC-002**: Recursive injection depth of at least 5 levels is supported without error.
- **SC-003**: 100% of Controller execution passes through the DI container for instantiation.
- **SC-004**: Properties annotated with `#[Inject]` are correctly populated with their dependencies upon class instantiation.
