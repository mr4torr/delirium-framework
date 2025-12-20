# Research: HTTP Routing & Attribute Controllers

## Architectural Decisions

### Attribute-Based Routing

- **Decision**: Use native PHP 8 Attributes (`#[Get]`, `#[Post]`, etc.) to define routes.
- **Rationale**: User explicitly requested NestJS-like behavior. Keeps routing logic co-located with controllers. Native reflection is fast in PHP 8+, especially with OpenSwoole's persistent process model (scan once at boot).
- **Alternatives Considered**: 
  - *YAML/XML Config*: Rejected (User request, robust but verbose).
  - *Fluent API (Laravel style)*: Rejected as primary method, though Router implementation might support it internally.

### 2. PSR-7 Bridge Strategy

- **Decision**: Implement a `SwooleStream` and `SwooleUploadedFile` implementation wrapping native Swoole objects, lazily populating the PSR-7 Request.
- **Rationale**: "Swoole-First" principle. Avoiding full copy of request body into memory if possible. Compatibility with `psr/http-message` is mandatory.
- **Unknowns Resolved**: We will need `nyholm/psr7` or `laminas/diactoros` as a factory implementation, or roll our own minimal implementation. *Recommendation: Use `nyholm/psr7` for performance.*

### 3. Dispatching Mechanism

- **Decision**: Router compiles routes into a dispatch table (Regex or Trie based).
- **Rationale**: High performance lookup.
- **Pattern**: Strategy Pattern for Dispatcher.

## Dependencies

- **Required**: `openswoole/core` (Runtime)
- **Required**: `psr/http-message` (Interfaces)
- **Recommended**: `nyholm/psr7` (Factory/Implementation)
