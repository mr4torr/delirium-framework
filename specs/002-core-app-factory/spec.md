# Feature Specification: Core Application Factory & Bootstrap

**Feature Branch**: `002-core-app-factory`
**Created**: 2025-12-20
**Status**: Draft
**Input**: User description: "Create a class responsible for starting the application similar to NestFactory.create... Create a bootstrap file inside public... with just one config file able to run the service."

## User Scenarios & Testing

### User Story 1 - Application Bootstrap (Priority: P1)

As a Developer, I want to initialize my application using a static factory method (like `DeliriumFactory::create()`), so that I can easily configure global settings (port, CORS) and start the server with minimal boilerplate.

**Why this priority**: Essential for Developer Experience (DX) and framework adoption. Reduces setup complexity.

**Independent Test**: Create a `public/index.php` that uses the Factory to start a server, and verify it responds to requests.

**Acceptance Scenarios**:

1. **Given** a new Delirium application, **When** I call `DeliriumFactory::create(AppModule::class)`, **Then** it returns an application instance ready to run.
2. **Given** configuration options (e.g., port 8080), **When** passed to the factory or create method, **Then** the underlying server binds to that port.
3. **Given** a `public/index.php` file, **When** executed via PHP CLI, **Then** the application starts and serves traffic.

---

### User Story 3 - Modular Architecture Definition (Priority: P1)

As a Developer, I want to define my application structure using an `#[AppModule]` attribute, so that I can declare imports, controllers, and providers in a centralized way (Declarative Dependency Injection).

**Why this priority**: Fundamental for organizing code in a modular way, enabling scalability and separation of concerns.

**Independent Test**: Define an `AppModule` with a controller and verify the factory registers it.

**Acceptance Scenarios**:

1. **Given** a class annotated with `#[Module(controllers: [MyController::class])]`, **When** the application starts, **Then** `MyController` is registered in the router.
2. **Given** a hierarchy of modules (e.g., `AppModule` imports `PublicModule` and `PrivateModule`), **When** the application starts, **Then** routes and providers from all nested modules are registered correctly.
3. **Given** a module deeply nested in the import graph, **When** scanned, **Then** its resources are available without manual registration in the root.

### Edge Cases

- **Port in Use**: What happens if the configured port is already occupied? (System should throw a bind exception and exit).
- **Missing Module**: What happens if `create()` is called without a valid Module class? (System should throw an `InvalidModuleException`).
- **Invalid Configuration**: What happens if an invalid CORS origin or negative port is provided? (System should validate options at startup).

## Requirements

### Functional Requirements

- **FR-001**: System MUST provide a `DeliriumFactory` class with a static `create` method.
- **FR-002**: The `create` method MUST accept a Root Module class and optional configuration array/object.
- **FR-003**: System MUST allow configuring the listening port (default 9501).
- **FR-004**: System MUST allow enabling/configuring CORS (Cross-Origin Resource Sharing).
- **FR-005**: System MUST provide a standard `public/index.php` (or similar bootstrap template) to serve as the entry point.
- **FR-006**: The Application instance MUST expose a `listen()` or `run()` method to start the OpenSwoole server.
- **FR-007**: The Factory MUST initialize the DI Container and register the Root Module.
- **FR-008**: System MUST provide an `#[AppModule]` attribute class.
- **FR-009**: The `#[AppModule]` attribute MUST accept an `imports` array for included modules.
- **FR-010**: The `#[AppModule]` attribute MUST accept a `controllers` array to register controllers.
- **FR-011**: The `#[AppModule]` attribute MUST accept a `providers` array for dependency injection services.
- **FR-012**: The Application Factory MUST recursively parse `imports` to build the complete application graph.

### Key Entities

- **DeliriumFactory**: Static entry point.
- **Application**: The runtime instance wrapping the Server and Container.
- **AppModule (Attribute)**: Metadata container for module structure (imports, controllers, providers).
- **AppOptions**: Value object or array for configuration (Port, Host, CORS).

## Success Criteria

### Measurable Outcomes

- **SC-001**: A developer can boot a "Hello World" application with 5 lines of code in `index.php`.
- **SC-002**: Changing the port in the create options changes the listening port of the server.
- **SC-003**: CORS headers are present in responses when enabled in configuration.

## Assumptions

- The `#[Module]` attribute exists or will be mocked/stubbed for this feature (based on recent `http-router` work where we defined `HttpRouterModule` as a class).
- We are using OpenSwoole as the HTTP server engine.
- "CORS" configuration implies at least allowing origins, methods, and headers.
