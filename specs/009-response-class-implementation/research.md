# Research: Response Class Implementation

**Feature**: 009-response-class-implementation

## Key Decisions

### 1. Inheritance vs Composition
**Decision**: Inheritance (`extends Nyholm\Psr7\Response`).
**Rationale**: 
- `ResponseInterface` has many methods (`withStatus`, `withHeader`, etc.).
- Composition (Decorator) would require proxying all these methods manually or using `__call`, which hurts static analysis and performance.
- Inheritance provides instant compatibility with any type-hint expecting `ResponseInterface` or even `Nyholm\Psr7\Response` (though we discourage concrete hints).
- The user requirement explicitly asked to "implement the interface ... and ensure use of ResponseInterface".

### 2. Polymorphic `body()`
**Decision**: Implement `body()` to handle `string`, `array`, `object`, `int`, `bool`.
**Context**: PSR-7 requires `withBody(StreamInterface $body)`.
**Approach**:
- `body($content)` will:
  - Detect type.
  - If array/object: `json_encode` + set Header if missing.
  - If scalar: cast to string.
  - Create a Stream (using `Nyholm\Psr7\Stream::create($string)`).
  - Call `withBody($stream)` internally.
  - Return `$this` (immutability check: actually `withBody` returns clone, so `body()` must return the NEW instance).

### 3. JsonResponse Class
**Decision**: Separate class.
**Rationale**:
- Provides explicit intent.
- Can set `Content-Type: application/json` in constructor.
- Can enforce array/object type in constructor if desired (or keep loose).

## Alternatives Considered
- **Traits**: Adding `BodyTrait` to `Response`. Rejected as over-engineering for a core class.
- **Static Factory**: `Response::json($data)`. Valid, but `JsonResponse` class is more standard in frameworks (Laravel/Symfony style).
