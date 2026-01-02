# Feature Specification: PSR-7 Support and Enhanced Routing Attributes

**Feature Branch**: `013-psr7-support`
**Created**: 2026-01-02
**Status**: Draft
**Input**: User description: Implement PSR-7 support in http-router specific Request/Response classes, Dependency Injection for controllers, and extended routing attributes for default status/type configuration.

## User Scenarios & Testing

### User Story 1 - Controller Dependency Injection (Priority: P1)

As a developer, I want to be able to type-hint `RequestInterface` and `ResponseInterface` in my controller methods or constructors so that I can directly access and manipulate the HTTP request and response using standard PSR-7 objects.

**Why this priority**: This is the core requirement for enabling testable, standard-compliant HTTP handling within the framework.

**Independent Test**: Create a controller with `public function index(RequestInterface $request, ResponseInterface $response)` and verify that the framework injects the correct instances when the route is hit.

**Acceptance Scenarios**:

1. **Given** a controller method signature `action(RequestInterface $request)`, **When** the route is dispatched, **Then** `$request` is a valid instance of `Psr\Http\Message\ServerRequestInterface` containing current request data.
2. **Given** a controller method signature `action(ResponseInterface $response)`, **When** the route is dispatched, **Then** `$response` is a valid instance of `Psr\Http\Message\ResponseInterface`.
3. **Given** an application container, **When** `$container->get('request')` is called, **Then** it returns the current `RequestInterface` instance.
4. **Given** an application container, **When** `$container->get('response')` is called, **Then** it returns a `ResponseInterface` instance.

---

### User Story 2 - Hyperf-Compatible Request API (Priority: P1)

As a developer, I want the `Request` object to provide helper methods similar to Hyperf (e.g., `input`, `header`, `cookie`, `file`) so that I can access request data intuitively without manually traversing the PSR-7 structure for common tasks.

**Why this priority**: Enhances developer experience and ease of adoption for users familiar with Hyperf or Laravel-like request objects.

**Independent Test**: Unit test the `Request` class methods against mock PSR-7 data.

**Acceptance Scenarios**:

1. **Given** a request with query params and parsed body, **When** `$request->input('key')` is called, **Then** it returns the value from body or query params, prioritizing body.
2. **Given** a request, **When** `$request->header('X-Custom')` is called, **Then** it returns the header value.
3. **Given** a request with specific content type files, **When** `$request->file('upload')` is called, **Then** it returns the `UploadedFileInterface` instance.
4. **Given** a request, **When** `$request->has('key')` is called, **Then** it returns true if the key exists in input.

---

### User Story 3 - Response Helper Methods (Priority: P1)

As a developer, I want the `Response` object to provide methods for cookies, redirects, and file downloads so that I can easily generate complex HTTP responses.

**Why this priority**: Essential for building real-world web applications that require session management, flow control, or file serving.

**Independent Test**: Unit test the `Response` class methods to verify they return a modified response object with correct headers/body.

**Acceptance Scenarios**:

1. **Given** a response object, **When** `$response->withCookie($cookie)` is called, **Then** the `Set-Cookie` header is correctly added.
2. **Given** a response object, **When** `$response->redirect('/target')` is called, **Then** it returns a response with status 302 and `Location: /target` header.
3. **Given** a response object, **When** `$response->download('/path/file')` is called, **Then** it returns a stream response with appropriate `Content-Disposition`.
4. **Given** a response object, **When** `$response->json($data)` is called, **Then** it returns a response with `Content-Type: application/json` and JSON-encoded body.

---

### User Story 4 - Declarative Route Configuration (Priority: P2)

As a developer, I want to define the default response `status` and `type` (JSON, XML, etc.) directly in the route attributes (e.g., `#[Get('/', type: 'json', status: 200)]`) so that I can reduce boilerplate code in controllers.

**Why this priority**: Improves code readability and reduces repetition for standard API endpoints.

**Independent Test**: Define routes with various attribute configurations and assert the response output matches the configuration without manual controller intervention.

**Acceptance Scenarios**:

1. **Given** a route `#[Get('/api', type: 'json')]` returning an array, **When** accessed, **Then** the response is JSON-encoded with `Content-Type: application/json`.
2. **Given** a route `#[Post('/create', status: 201)]`, **When** accessed, **Then** the response status code is 201.
3. **Given** a route with defined defaults, **When** the controller manually returns a `Response` object, **Then** the manual response overrides the attribute defaults.
4. **Given** a route with `type: 'xml'`, **When** accessed, **Then** the returned data is serialized to XML.

---

### Edge Cases

- **Invalid Attribute Type**: What happens if a developer specifies `type: 'invalid'`? System should throw a meaningful exception or fallback to JSON (decision: Exception).
- **Body Parsing Failures**: How does `$request->input()` handle invalid JSON bodies? Should return null or throw? Hyperf returns null/empty.
- **Header Case Sensitivity**: `$request->header()` must be case-insensitive per PSR-7.
- **Large File Downloads**: `$response->download()` must use streams to avoid memory exhaustion for large files.

## Requirements

### Functional Requirements

- **FR-001**: The system MUST provide a `Request` class that implements `Psr\Http\Message\ServerRequestInterface`.
- **FR-002**: The `Request` class MUST implement methods `input`, `header`, `cookie`, `query`, `post`, `file` per Hyperf documentation semantics.
- **FR-003**: The system MUST provide a `Response` class that implements `Psr\Http\Message\ResponseInterface`.
- **FR-004**: The `Response` class MUST provide methods for `json`, `xml`, `raw`, `redirect`, `download`, and cookie management per Hyperf documentation semantics.
- **FR-005**: The `ContainerInterface` MUST resolve 'request' to the current `Request` instance and 'response' to a new `Response` instance.
- **FR-006**: Route attributes (`Get`, `Post`, etc.) MUST accept `status` (int) and `type` (string) parameters.
- **FR-007**: The system MUST support `type` values: 'json', 'xml', 'html', 'stream', 'raw'.
- **FR-008**: The default `type` for routes MUST be 'json' if not specified (per user requirement "Por padrão... ser JSON"). *Assumption: This applies to API routes or globally? User says "Por padrão, o tipo de conteúdo deverá ser JSON". I will assume this is the default for the updated attributes.*
- **FR-009**: The framework MUST respect explicit response objects returned data over attribute defaults.
- **FR-010**: The `Request` and `Response` classes MUST NOT expose static methods for accessing state; all access MUST be via instance methods on injected objects.
- **FR-011**: The system MUST provide a `Delirium\Http\RequestInterface` extending `Psr\Http\Message\ServerRequestInterface` including helper methods.
- **FR-012**: The system MUST provide a `Delirium\Http\ResponseInterface` extending `Psr\Http\Message\ResponseInterface` including helper methods.
- **FR-013**: The implementation MUST utilize `nyholm/psr7` classes internally (via composition) and use `Psr17Factory` for object instantiation.
- **FR-014**: The system MUST implement a `ResponseResolverChain` invoked after controller execution to transform return values into `ResponseInterface` objects.
- **FR-015**: Specific Response Resolvers (JSON, XML, Stream, etc.) MUST be implemented in `packages/http-router/src/Resolver/Response` and respect Route Attributes (status, type).
- **FR-016**: The system MUST be fully compliant with **PSR-17 (HTTP Factories)**, ensuring all message creation uses standard factory interfaces.

### Success Criteria

- **SC-001**: 100% of defined `Request` and `Response` helper methods are implemented and covered by unit tests.
- **SC-002**: Controllers can successfully receive injected `RequestInterface` and `ResponseInterface`.
- **SC-003**: Routes configured with `type: 'json'` automatically serialize array returns to JSON.
- **SC-004**: Routes configured with `status` code correctly return that status code.

## Clarifications

### Session 2026-01-02
- Q: How should developers type-hint to access helper methods? → A: Extended Interfaces (`Delirium\Http\RequestInterface` extending PSR-7).
- Q: Implementation Strategy? → A: Reuse `nyholm/psr7` via Composition/Adapter pattern (classes are final).
- Q: Response Processing Strategy? → A: Post-invocation handling via `ResponseResolverChain` in `RegexDispatcher`. Create specific Resolvers for each return type in `src/Resolver/Response`.
- Q: Standards Compliance? → A: Strict adherence to PSR-7 (HTTP Message) and PSR-17 (HTTP Factories).

### Assumptions
- `Delirium\Http\Request` and `Response` will implement `Delirium\Http\RequestInterface` and `Delirium\Http\ResponseInterface`.
- `Nyholm\Psr7\Factory\Psr17Factory` will be used for object creation to satisfy PSR-17.
- Response resolution happens *after* controller execution, wrapping the result.
