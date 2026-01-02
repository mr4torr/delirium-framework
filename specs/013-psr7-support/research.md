# Research: PSR-7 Support and Implementation Strategy

## Decisions

### 1. Composition over Inheritance for PSR-7
- **Decision**: Use Composition/Adapter pattern for `Delirium\Http\Request` and `Delirium\Http\Response`.
- **Rationale**: `nyholm/psr7` classes (`ServerRequest`, `Response`) are `final` (or effectively so in practice for optimization) and inheritance is discouraged in favor of composition to allow wrapping standard PSR-7 instances (e.g. from other factories or middleware) with our helper methods.
- **Alternatives Considered**:
    - *Inheritance*: Not possible if classes are final; tightly couples to Nyholm implementation.
    - *Traits*: Mixins could work but don't solve the interface type-hinting issue effectively without a base class.

### 2. Interface Extension
- **Decision**: Define `Delirium\Http\Contract\RequestInterface` extends `Psr\Http\Message\ServerRequestInterface`.
- **Rationale**: User explicitly requested helper methods (`input()`, `json()`, etc.) to be available via type-hinting. Standard PSR-7 interfaces do not have these.
- **Implication**: Developers must type-hint the Delirium interface to get IDE autocompletion for helpers, or cast standard PSR-7 objects.

### 3. Response Resolution Chain
- **Decision**: Implement a `ResponseResolverChain` in `RegexDispatcher` invoked *after* the controller method returns.
- **Rationale**: Allows controllers to return various types (arrays, strings, objects) which are then transformed into `ResponseInterface` based on Route Attributes (`type`, `status`). Keeps controllers clean.
