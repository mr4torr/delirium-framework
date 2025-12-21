# Research: Router Scanner & PSR-7 Optimization

**Feature**: 004-optimize-router
**Date**: 2025-12-20

## 1. Scanner Optimization (`roave/better-reflection`)

### User Request
"Verify the viability of using a package like `roave/better-reflection` to ensure PSR-4 compliance."

### Findings
- **Purpose**: `roave/better-reflection` is primarily a static analysis tool tailored for inspecting code *without* loading it, or modifying ASTs on the fly.
- **Performance**: It is significantly slower than native PHP reflection and consumes substantial memory (parsing ASTs via `nikic/php-parser`). It is explicitly marked as "NOT suited to runtime usage" by its maintainers for typical application flows.
- **Use Case**: Booting the HTTP Router is a critical hot path (even if once per process). Adding a heavy AST parser purely for class discovery is disproportionate.
- **Compliance**: PSR-4 compliance can be achieved without full reflection. We only need to map `File Path` -> `Class Name`.

### Recommendation
**REJECT** `roave/better-reflection` for runtime scanning.
**PROPOSE** a robust `Token`-based scanner (using `token_get_all`) or `composer/class-map-generator` (dev dependency, but can be used in prod if lightweight).
*Decision*: Implement a robust `TokenScanner` within the package that correctly handles:
- Multiple namespaces
- PSR-4 structure
- Comments/Whitespace
*Rationale*: Zero-dependency (or low dependency), high performance, sufficient for purpose.

## 2. PSR-7 Usage (`nyholm/psr7`)

### User Request
"Evaluate the possibility of optimizing `nyholm/psr7` usage... considering compliance with PSR-7/17/18."

### Findings
- **Current State**: `http-router` currently relies on `nyholm/psr7` for implementations.
- **Search Results**: Direct instantiation (`new Response`) was found in `tests/` but not in `src/`.
- **Dependency**: The package `composer.json` requires `nyholm/psr7`.
- **Optimization**: The best practice is to depend on `psr/http-factory-implementation` and inject the factory, OR use `nyholm/psr7` purely as the implementation logic via its Fabory.

### Recommendation
- **Strict Factories**: Ensure `SwoolePsrAdapter` uses `Psr\Http\Message\ServerRequestFactoryInterface` and `ResponseFactoryInterface`.
- **Decoupling**: Change `composer.json` to require `psr/http-factory`. Keep `nyholm/psr7` as a "provider" of that interface but code against the interface.

## Decisions Log

| Topic | Decision | Details |
|-------|----------|---------|
| Scanner Lib | **Custom Tokenizer** | `roave/better-reflection` is too heavy (800MB+ peak mem potential). We will improve the existing `getClassFromFile` method to use `token_get_all` correctly instead of Regex. |
| PSR-7 Usage | **Interfaces** | Refactor `SwoolePsrAdapter` to accept `Psr\Http\Message\ServerRequestFactoryInterface` in constructor. |
