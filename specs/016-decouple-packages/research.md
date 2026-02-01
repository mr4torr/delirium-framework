# Research: Decouple Packages

## 1. Deptrac Configuration
**Decision**: Use `qossmic/deptrac` with a `depfile.yaml` in the root directory.
**Rationale**: It is the industry standard for PHP architectural testing. It allows defining "Layers" (one per package) and a "Ruleset" (whitelist of allowed interactions).
**Configuration Strategy**:
- **Layers**:
  - `Core`: `Delirium\Core\*`
  - `Http`: `Delirium\Http\*`
  - `Di`: `Delirium\DI\*`
  - `Validation`: `Delirium\Validation\*`
  - `Support`: `Delirium\Support\*`
  - `DevTools`: `Delirium\DevTools\*`
  - `Vendor`: `Vendor\*`
- **Ruleset**:
  - `Http` allows `Support`, `Di` (via `Psr\Container`), `Vendor`. **Forbidden**: `Core`.
  - `Di` allows `Support`, `Vendor`. **Forbidden**: `Core`.
  - `Validation` allows `Support`, `Vendor`. **Forbidden**: `Core`.
  - `Core` allows `Http`, `Di`, `Validation`, `Support`, `Vendor`. (Glue layer).
  - `Support` allows `Vendor`. **Forbidden**: `Core`, `Http`, `Di`.
  - `DevTools` allows `Vendor`. **Forbidden**: `Core` (Strict one-way).

## 2. Support Package (`packages/support`)
**Decision**: Create a new package `delirium/support`.
**Rationale**: Provide a home for shared utilities without circular dependencies.
**Alternatives Considered**:
- *Duplication*: Rejected due to maintenance burden.
- *Use Core*: Rejected as it violates decoupling goals.
**Initial Contents**:
- `str_contains`, `str_starts_with` polyfills (if any legacy needed, unlikely for PHP 8.4).
- Custom `Arr` and `Str` helpers if currently used in Core/Router.
- Shared Exceptions or Contracts if they don't fit in PSRs.

## 3. DevTools Strategy
**Decision**: `packages/dev-tools` is a library, not a dependency.
**Rationale**: It contains debug tools (like `dd` clones or testing traits).
**Implementation**:
- Root `composer.json` `require-dev` includes `delirium/dev-tools`.
- Component `composer.json` (e.g., `packages/http-router/composer.json`) MAY `require-dev` it if used in tests.
- `delirium/dev-tools/composer.json` must NOT require `delirium/core`.

## 4. PSR Usage vs Support Contracts
**Decision**: Prefer PSRs. Fallback to `Delirium\Support\Contract\*` for framework-specific needs.
**Rationale**: PSRs (`psr/container`, `psr/http-message`) are standard. Custom contracts in `Support` allow sharing `Delirium\Support\Contract\Jsonable` without pulling in Core.
