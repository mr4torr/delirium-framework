# Implementation Plan - Optimize Router Scanner

**Feature**: 004-optimize-router
**Status**: Planning

## Technical Context

### 1. Attribute Scanner Optimization
The current `AttributeScanner` uses regex to find classes. This is fragile. The user suggested `roave/better-reflection`.
- **Research Result**: `roave/better-reflection` is too heavy for runtime.
- **Decision**: Refactor `AttributeScanner` to use PHP's `token_get_all()` (Tokenizer) to robustly identify the Namespace and Class name from a file. This is fast, robust, and zero-dependency.

### 2. PSR-7 Optimization
We want to decouple `nyholm/psr7` concrete classes.
- **Decision**: Update `SwoolePsrAdapter` to use `Psr\Http\Message\ServerRequestFactoryInterface` etc.
- **Dependency**: Add `psr/http-factory` to `composer.json`.

## Constitution Check

| Principle | Check | status |
|-----------|-------|--------|
| **Swoole-First** | Scanner runs at boot (OK). Factories are standard. | ✅ |
| **Design Patterns** | **Factory Pattern** for PSR-7 creation. | ✅ |
| **Stateless** | Scanner is stateless. | ✅ |
| **Strict Types** | All new code will be strict. | ✅ |
| **Modular** | `http-router` is a package. | ✅ |

## Proposed Changes

### [MODIFY] [packages/http-router/composer.json](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/composer.json)
- Add `psr/http-factory` to require.

### [MODIFY] [packages/http-router/src/Scanner/AttributeScanner.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Scanner/AttributeScanner.php)
- Replace `getClassFromFile` regex logic with `token_get_all` state machine loop for robustness.
- Ensure it handles:
    - Namespace declarations (simple and bracketed).
    - Class declarations.
    - Skips comments/whitespace.

### [MODIFY] [packages/http-router/src/Bridge/SwoolePsrAdapter.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/http-router/src/Bridge/SwoolePsrAdapter.php)
- Inject `ServerRequestFactoryInterface`, `StreamFactoryInterface`, `UploadedFileFactoryInterface`.
- Remove hardcoded `new Nyholm\Psr7\...`.

### [MODIFY] [packages/core/src/AppFactory.php](file:///home/mr4torr/Project/delirium/delirium-framework/packages/core/src/AppFactory.php)
- Update instantiation of `SwoolePsrAdapter` (or DI container config) to provide the Nyholm factories.

## Verification Plan

### Automated Tests
- **Unit**: Create `AttributeScannerTest` with fixtures (complex PHP files, deep namespaces).
- **Unit**: Verify `SwoolePsrAdapter` uses injected factories (mock interfaces).
- **Integration**: Run `composer test` to ensure router still works.
