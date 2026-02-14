# Feature Specification: Route List Command

**Feature**: Route List Command
**Status**: DRAFT
**Created**: 2026-02-14

## Goal

Implement a `route:list` console command in the `http-router` package to display all registered routes in the application. This command will be registered via a new `ServiceProvider` in the `http-router` package, which will be loaded by the Core package.

## User Scenarios

1.  **Developer lists routes**:
    -   User runs `bin/console route:list`.
    -   The console displays a formatted table with columns: `Method`, `URI`, `Handler`.
    -   The command successfully retrieves routes from the application's router registry.

## Functional Requirements

### 1. Route Service Provider
-   **Component**: `Delirium\Http\HttpRouterServiceProvider`
-   **Location**: `packages/http-router/src/HttpRouterServiceProvider.php`
-   **Inheritance**: Extends `Delirium\Support\ServiceProvider`.
-   **Responsibilities**:
    -   `register()`: Bind the `RouteListCommand` to the container.
    -   `boot()`: Register the command with the Console Kernel if the application is running in console mode.
-   **Dependencies**: Uses `Delirium\Core\Console\Kernel` to register the command.

### 2. Route List Command
-   **Component**: `Delirium\Http\Console\Command\RouteListCommand`
-   **Location**: `packages/http-router/src/Console/Command/RouteListCommand.php`
-   **Inheritance**: Extends `Symfony\Component\Console\Command\Command`.
-   **Configuration**:
    -   Name: `route:list`
    -   Description: "List all registered routes"
-   **Execution**:
    -   Retrieves `Delirium\Http\Contract\RouterInterface` from the container.
    -   Accesses the route registry to fetch all defined routes.
    -   Displays routes in a table format using Symfony Console `Table` helper.
    -   Columns: `Method`, `URI`, `Handler` (Display class+method or "Closure").

### 3. Core Integration
-   **Component**: `Delirium\Core\Foundation\ProviderRepository`
-   **Requirement**: The `Delirium\Http\HttpRouterServiceProvider` must be registered as a default provider in the Core package.
-   **Change**: Update the `$providers` property in `ProviderRepository` to include `Delirium\Http\HttpRouterServiceProvider::class` in the `all` environment list.

## Assumptions
-   The `Delirium\Http\Router` class or its interface provides access to the `RouteRegistry` or a method to retrieve routes (verified: `getRegistry()` exists on `Router`).
-   The `Router` is available in the dependency injection container.
-   `Delirium\Core\Console\Kernel` provides a singleton instance or static access to add commands dynamically.

## Success Criteria
-   Running `bin/console route:list` outputs a table of routes.
-   The `ServiceProvider` is automatically loaded without manual intervention in `bin/console` or `public/index.php`.
-   Dependencies are correctly injected (Router into Command).
