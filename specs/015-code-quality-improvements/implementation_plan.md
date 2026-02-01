# Refactoring Plan: Code Quality Improvements (SOLID/DRY)

**Feature**: 015-code-quality-improvements

## Goal
Improve codebase maintainability, testability, and adherence to SOLID/DRY/Object Calisthenics principles by refactoring complex classes and reducing coupling.

## Problem Analysis

### 1. `AppFactory.php` (God Class / SRP Violation)
- **Issues**: Handles container construction, module scanning, service registration, and application bootstrapping. deeply coupled to specific implementations.
- **Solution**: Extract responsibilities.
    - Extract `ModuleScanner` to `Delirium\Core\Module\ModuleScanner`.
    - Extract Container setup to `Delirium\Core\Container\ContainerFactory`.

### 2. `RegexDispatcher.php` (Complex / Mixed Concerns)
- **Issues**: Handles route storage, route matching (regex logic), dependency resolution, and controller invocation. High indentation levels.
- **Solution**: Split into specialized components.
    - Extract Matching logic to `Delirium\Http\Routing\Matcher\RegexRouteMatcher`.
    - Extract Invocation logic to `Delirium\Http\Invoker\ControllerInvoker`.
    - `RegexDispatcher` becomes a coordinator.

## User Review Required
> [!IMPORTANT]
> This is a significant structural refactor. Tests must be green at every step.
> No public API changes intended, but internal component wiring will change.

## Proposed Changes

### Core Package (`packages/core`)

#### [NEW] `Delirium\Core\Module\ModuleScanner`
- Moves `scanModule` logic out of `AppFactory`.
- Responsible for recursive module scanning and updating the `ContainerBuilder`.

#### [NEW] `Delirium\Core\Container\ContainerFactory`
- Encapsulates the logic for creating/caching the ContainerBuilder.
- Reduces `AppFactory` to a simplified coordinator.

#### [MODIFY] `packages/core/src/AppFactory.php`
- Delegate work to the new classes.

### HTTP Router Package (`packages/http-router`)

#### [NEW] `Delirium\Http\Routing\Matcher\RouteMatcherInterface` & `RegexRouteMatcher`
- Encapsulates `addRoute`, `staticRoutes`, `dynamicRoutes` and the `match(Request): RouteResult` logic.

#### [NEW] `Delirium\Http\Invoker\ControllerInvoker`
- Encapsulates `invokeWithReflection`, `executeHandler`, and argument/response resolution chain coordination.

#### [MODIFY] `packages/http-router/src/Dispatcher/RegexDispatcher.php`
- Inject `RouteMatcherInterface` and `ControllerInvoker`.
- `dispatch` becomes: `Matcher->match()` -> `Invoker->invoke()`.

## Verification Plan

### Automated Tests
- `composer test` must pass after each extraction.
- Create new unit tests for `ModuleScanner`, `RouteMatcher`, `ControllerInvoker`.

### Manual Review
- Verify `AppFactory` length is reduced significantly.
- Verify `RegexDispatcher` complexity (cyclomatic complexity) is reduced.
