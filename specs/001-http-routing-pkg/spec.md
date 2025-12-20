# Feature Specification: HTTP Routing & Attribute Controllers

**Feature Branch**: `001-http-routing-pkg`
**Created**: 2025-12-20
**Status**: Draft
**Input**: User description: "Crie dentro de packages um novo projeto php onde ele será responsavel pelo HttpClient, ou seja responsavel pelo mapeamento de rotas. Nele devera ter atributos PHP get, post, put, path, delete, options, trace e head para e eles serão utilizado sobre as funções e classes definidas como Controllers, semelhante ao projeto NestJs https://docs.nestjs.com/controllers, esse projeto deve seguir o PSR-7, mas utilizando openswoole"

## Assumptions
- **Terminology**: The user used the term "HttpClient" but described functionality (route mapping, controllers, NestJS comparison) that belongs to an **HTTP Server/Router**. This spec treats the request as building a **Server-side Routing Component**.
- **Scope**: This feature covers the *routing logic*, *attribute definitions*, and *dispatching mechanism*. It includes the bridge between OpenSwoole requests and PSR-7 objects.

## User Scenarios & Testing

### User Story 1 - Defining Routes with Attributes (Priority: P1)

As a Developer, I want to define HTTP routes using PHP Attributes directly on my Controller classes and methods, so that my routing logic is co-located with my business logic (similar to NestJS).

**Why this priority**: Core value proposition. Without this, the package functionality doesn't exist.

**Independent Test**: Create a class with attributes, inspect it via the Router, and verify routes are registered.

**Acceptance Scenarios**:

1. **Given** a Controller class with `#[Controller('/users')]` and a method with `#[Get('/profile')]`, **When** the router parses the class, **Then** a route `GET /users/profile` is registered.
2. **Given** a method with `#[Post]`, **When** mapped, **Then** it registers a POST route.
3. **Given** attributes supporting HTTP verbs (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD), **When** used, **Then** the corresponding HTTP methods are enforced.

---

### User Story 2 - PSR-7 & OpenSwoole Bridge (Priority: P2)

As a Developer, I want incoming OpenSwoole requests to be converted to PSR-7 `ServerRequestInterface` objects automatically, so that my controllers remain standard-compliant and decoupled from the Swoole runtime.

**Why this priority**: Ensures interoperability and adheres to the "PSR-7" requirement while using "OpenSwoole".

**Independent Test**: Send a mock OpenSwoole request and verify the controller receives a valid PSR-7 Request object.

**Acceptance Scenarios**:

1. **Given** an OpenSwoole `Request` object, **When** processed by the Dispatcher, **Then** the target Controller method receives a generic `Psr\Http\Message\ServerRequestInterface` instance.
2. **Given** a Controller returns a `Psr\Http\Message\ResponseInterface`, **When** sending the response, **Then** it is correctly converted back to an OpenSwoole response (status, headers, body).

---

### User Story 3 - Route Parameters and Dispatching (Priority: P3)

As a Developer, I want to define dynamic route parameters in the path (e.g., `/users/:id`), so that I can capture values from the URL.

**Why this priority**: Essential for real-world applications.

**Independent Test**: Register a generic route with parameters and simulate a matching request.

**Acceptance Scenarios**:

1. **Given** a route defined as `#[Get('/users/{id}')]` (or `:id`), **When** a request hits `/users/123`, **Then** the controller receives `123` as an argument.

### Edge Cases

- **Duplicate Routes**: What happens if two controllers define the same method and path? (System should throw a Boot Exception).
- **Method Not Allowed**: What happens if a URL matches a path but not the method? (Should return 405).
- **Route Not Found**: What happens if no route matches? (Should return 404).
- **Invalid Parameters**: What happens if a route expects an int `{id}` but executes with a string? (Depends on validation layer, but router passes string by default).

## Requirements

### Functional Requirements

- **FR-001**: System MUST provide PHP 8 Attributes for HTTP Methods: `Get`, `Post`, `Put`, `Delete`, `Patch`, `Options`, `Head`, `Trace`.
- **FR-002**: System MUST provide a `Controller` (or `Path`) attribute to define route prefixes at the class level.
- **FR-003**: System MUST implement a `Router` that scans classes for these attributes to build the routing table.
- **FR-004**: System MUST handle OpenSwoole Requests and convert them to PSR-7 Requests.
- **FR-005**: System MUST handle PSR-7 Responses and emit them to the OpenSwoole Response.
- **FR-006**: The package MUST be isolated in `packages/` directory (e.g., `packages/http` or `packages/router`).

### Key Entities

- **RouteAttribute**: Base class for metadata Attributes.
- **Router**: Service responsible for matching URL/Method to a Handler.
- **Dispatcher**: Service that executes the handler.
- **ContextAdapter**: Bridge between Swoole and PSR-7.

## Success Criteria

### Measurable Outcomes

- **SC-001**: A developer can define a functional "Hello World" API with 1 controller and 1 attribute in less than 10 lines of user code.
- **SC-002**: System correctly routes 100% of standard HTTP verbs supported by Attributes.
- **SC-003**: Unit tests confirm 100% type compatibility with `ServerRequestInterface` in controllers.
