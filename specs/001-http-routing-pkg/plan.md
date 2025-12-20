# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: PHP 8.4
**Primary Dependencies**: openswoole/core, psr/http-message, psr/container
**Storage**: N/A (Stateless Routing)
**Testing**: PHPUnit 12.5
**Target Platform**: Linux (OpenSwoole requirement)
**Project Type**: Library/Package
**Performance Goals**: High throughput dispatching, minimal overhead per request.
**Constraints**: Must be non-blocking, strict type safety, zero memory leaks.
**Scale/Scope**: Core framework component.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **I. Swoole-First**: Routing logic must not block. Usage of `Context` for request data.
- [x] **II. Design Patterns**: Uses **Attribute** (Meta-prog), **Adapter** (Swoole->PSR7), **Strategy** (Dispatching).
- [x] **III. Stateless**: Router is singleton (immutable config), Dispatcher handles state via Request object.
- [x] **IV. Strict Contracts**: Uses PSR-7 interfaces. PHP 8.4 strict types.
- [x] **V. Modular Architecture**: This IS a module/package.
- [x] **VI. Attribute-Driven**: Core feature is attribute-based routing.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
# Option: Package Structure
packages/http-router/
├── composer.json
├── src/
│   ├── Attribute/           # @Get, @Post, @Controller
│   ├── Exception/
│   ├── Router.php           # Scans and builds routes
│   ├── Dispatcher.php       # Matches request -> handler
│   └── Bridge/              # Swoole <-> PSR-7
└── tests/
    ├── Unit/
    └── Integration/

```

**Structure Decision**: Creating a new package in `packages/http-router/` to enforce modularity and separation of concerns as requested.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
