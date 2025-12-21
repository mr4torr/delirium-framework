# Feature Specification: Response Class Implementation

**Feature Branch**: `009-response-class-implementation`
**Created**: 2025-12-21
**Status**: Draft
**Input**: User description: "Crie uma classe no núcleo denominada Response, que implemente a interface Nyholm\Psr7\Response e seja compatível com o contrato Psr\Http\Message\ResponseInterface. A classe deve incluir um método body, que permita a definição de conteúdo em diversos formatos... Além disso, é necessário que a classe aceite um segundo parâmetro... para status."

## User Scenarios & Testing

### User Story 1 - Simplified Response Creation (Priority: P1)

As a developer, I want a `Delirium\Http\Response` class that allows me to easily set the response body with mixed data types (arrays, objects) and status code directly, so that I don't have to manually manage streams or serialization for every response.

**Why this priority**: Improves Developer Experience (DX) significantly by reducing boilerplate code in controllers.

**Independent Test**:
1. Instantiate `new Response(['foo' => 'bar'], 201)`.
2. Verify `getStatusCode()` is 201.
3. Verify `getBody()->getContents()` contains valid JSON string `{"foo":"bar"}`.
4. Verify object is instance of `Psr\Http\Message\ResponseInterface`.

**Acceptance Scenarios**:

1. **Given** a new `Response` instance initialized with an array `['a' => 1]`, **Then** the body MUST be serialized to JSON automatically.
2. **Given** a new `Response` instance initialized with a string "hello", **Then** the body MUST contain "hello".
3. **Given** the initialization `new Response($body, 404)`, **Then** the status code MUST be 404.
4. **Given** an object implementing `JsonSerializable` passed to `body()`, **Then** it MUST be serialized to JSON.
5. **Check** that the class is compatible with `Nyholm\Psr7\Response` (extends or adapts) and implements `ResponseInterface`.

## Requirements

### Functional Requirements

- **FR-001**: The `Delirium\Http\Response` class MUST implement `Psr\Http\Message\ResponseInterface`.
- **FR-002**: The `Delirium\Http\Response` constructor MUST match `Nyholm\Psr7\Response` signature: `__construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)`.
- **FR-002-B**: A separate `Delirium\Http\JsonResponse` class SHOULD be created for explicit JSON responses, handling `Content-Type` header automatically.
- **FR-003**: The class MUST provide a `body(mixed $content)` method that sets the stream content.
- **FR-004**: Data types accepted by `body()` MUST include:
  - `string`: set as is.
  - `array` / `object`: serialized to JSON.
  - `int` / `float` / `bool`: cast to string.
  - `null`: empty body.
- **FR-005**: When `body()` receives an array or object, it MUST serialize to JSON and set `Content-Type: application/json` if not already present.

## Clarifications

### Session 2025-12-21
- Q: How should we simplify creation if the constructor structure must be preserved?
- A: **Option C & D**: Keep standard constructor logic (Option C) and Provide a separate `JsonResponse` class (Option D).
  - `new Response()` follows PSR-7 signature.
  - `body()` method simplifies content setting post-instantiation.
  - `JsonResponse` is available for explicit JSON usage.

### Key Entities

- **Delirium\Http\Response**: The new class extending `Nyholm\Psr7\Response`.
- **Delirium\Http\JsonResponse**: Specialized class for JSON responses.

## Success Criteria

### Measurable Outcomes

- **SC-001**: Controllers can return `new Response($data)` without manual stream handling.
- **SC-002**: PSR compliance tests pass for the new class.
- **SC-003**: Unit tests verify correct serialization for Arrays, Objects, and Strings.

## Assumptions

- We will extend `Nyholm\Psr7\Response` directly if technically feasible (property promotion might conflict, but we can override constructor).
- User prefers `new Response($body, $status)` signature over PSR-7 standard `new Response($status, $headers, $body)`. This is a "Framework Response" wrapper.
