# Research: Refactoring Strategy for Inline Imports

## Decision 1: Refactoring Approach

**Decision**: Manual refactoring with regex-assisted search and IDE refactoring tools.

**Rationale**:
- The codebase is relatively small (~50-100 files).
- PHP has complex namespace resolution rules (aliases, name collisions).
- Automated tools (e.g., PHP-CS-Fixer, Rector) can handle simple cases but may introduce errors with edge cases.
- Manual review ensures correctness and adherence to Constitution standards.

**Alternatives Considered**:
- **Fully Automated (PHP-CS-Fixer/Rector)**: Risk of incorrect transformations, especially with name collisions.
- **Custom Script**: High development cost for one-time refactor.
- **Hybrid (Regex + Manual)**: Selected approach balances speed and safety.

## Decision 2: Handling Name Collisions

**Decision**: Use aliasing (`use Foo\Bar as FooBar;`) when class names collide.

**Rationale**:
- Preserves readability.
- Follows PSR-12 conventions.
- Avoids ambiguity in code.

**Alternatives Considered**:
- **Keep inline FQN for collisions**: Violates Constitution Principle VIII.
- **Rename classes**: Out of scope for this refactor.

## Decision 3: Validation Strategy

**Decision**: Run full test suite (`composer test`) after each package refactor.

**Rationale**:
- Ensures zero regressions (FR-003).
- Incremental validation reduces risk.
- Fast feedback loop.

**Alternatives Considered**:
- **Refactor all at once**: Higher risk of cascading failures.
- **Static analysis only**: Insufficient to catch runtime issues.

## Decision 4: Scope Boundaries

**Decision**: Refactor only `packages/` directory. Exclude `vendor/`, `build/`, `.specify/`.

**Rationale**:
- `packages/` contains framework code under our control.
- `vendor/` is third-party code (not our responsibility).
- Other directories are tooling/build artifacts.

**Alternatives Considered**:
- **Include all PHP files**: Unnecessary and risky for non-framework code.

## Implementation Notes

### Regex Pattern for Detection

```regex
(new|extends|implements|instanceof|catch|throw new)\s+\\[A-Z][\w\\]*
```

This pattern detects inline FQNs in common contexts.

### Refactoring Checklist (Per File)

1. Identify all inline FQNs using regex.
2. Extract unique namespaces.
3. Add `use` statements at top of file (after `declare(strict_types=1);`).
4. Replace inline FQNs with short names.
5. Handle collisions with aliases.
6. Verify file compiles (`php -l <file>`).
7. Run tests for affected package.

### Order of Refactoring

1. `packages/http-router/` (largest, most complex)
2. `packages/core/`
3. `packages/validation/`
4. `packages/dependency-injection/`
5. Other packages as needed

This order prioritizes high-impact areas first.
