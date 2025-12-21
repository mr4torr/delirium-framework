# Walkthrough - Feature 007: Use PSR Interfaces in Method Injection

**Feature**: Refactor injection to use PSR interfaces (`Psr\Container\ContainerInterface`, `Psr\Http\Message\ServerRequestInterface`).
**Branch**: `007-use-psr-interfaces`
**Status**: Completed

## Changes

### 1. New Demonstration Controller
- Created `src/Example/PsrInjectionController.php`.
- Demonstrates injection of:
  - `Psr\Container\ContainerInterface` via Constructor.
  - `Psr\Http\Message\ServerRequestInterface` via Method Injection.

### 2. Refactoring
- Refactored `src/Example/ExampleController.php` to include `ServerRequestInterface` in the `methodInjection` action, demonstrating usage alongside other service injections.

### 3. Verification
- **Audit**: Checked `src/` for direct usages of `OpenSwoole\Http\Request` or `Response`. Found none.
- **Resolvers**: Verified `ContainerServiceResolver` and `ServerRequestResolver` in `packages/http-router` correctly handle PSR interfaces.

## Verification Results

### Automated Tests
Ran `tests/Feature/PsrInjectionTest.php` via `phpunit`.
- **Result**: `OK (1 test, 3 assertions)`
- **Scope**: Verified controller instantiation and method injection logic.
