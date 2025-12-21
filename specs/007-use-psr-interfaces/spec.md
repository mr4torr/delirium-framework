# Feature Specification: Use PSR Interfaces in Method Injection

**Feature Branch**: `007-use-psr-interfaces`
**Created**: 2025-12-21
**Status**: Draft
**Input**: User description: "Analisar e ajustar os arquivos para que utilize interfaces do PSR na injeção dos métodos"

## User Scenarios & Testing

### User Story 1 - Injection Interoperability (Priority: P1)

As a developer, I want to type-hint PSR interfaces in my controller actions and service constructors so that my code is decoupled from specific framework implementations.

**Why this priority**: Core architectural requirement for a flexible, standard-compliant framework.

**Independent Test**: Create a route that injects `Psr\Http\Message\ServerRequestInterface` and `Psr\Container\ContainerInterface` and asserts they are resolved correctly.

**Acceptance Scenarios**:

1. **Given** a controller method signature `public function action(ServerRequestInterface $request)`, **When** the route is matched, **Then** a PSR-7 request object is injected.
2. **Given** a service constructor `public function __construct(ContainerInterface $container)`, **When** the service is instantiated, **Then** the DI container is injected.
3. **Check** that no user-land code (in `src/`) relies on `OpenSwoole` classes directly for injection.

## Requirements

### Functional Requirements

- **FR-001**: The framework MUST provide `Psr\Container\ContainerInterface` for injection in services and controllers.
- **FR-002**: The router MUST resolve `Psr\Http\Message\ServerRequestInterface` for controller action arguments.
- **FR-003**: User-land code (Controllers/Services) SHOULD NOT type-hint concrete infrastructure classes (e.g., `OpenSwoole\Http\Request`, `Symfony\Component\DependencyInjection\ContainerBuilder`) when a PSR interface is available.
- **FR-004**: Existing usage of concrete classes in `src/` examples MUST be refactored to use PSR interfaces.

### Key Entities

- **PSR Interfaces**: `Psr\Container\ContainerInterface`, `Psr\Http\Message\ServerRequestInterface`, `Psr\Http\Message\ResponseInterface`.

## Success Criteria

### Measurable Outcomes

- **SC-001**: 100% of method injections in `src/` use PSR interfaces where applicable.
- **SC-002**: Automated test confirms `ServerRequestInterface` injection works in a controller action.
- **SC-003**: Automated test confirms `ContainerInterface` injection works in a service.

## Assumptions

- We are using `nyholm/psr7` for PSR-7 implementation.
- We are using `symfony/dependency-injection` which supports PSR-11.
