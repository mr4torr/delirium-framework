# Research: SPC Compiler Package

**Feature**: SPC Compiler Package (011-spc-compiler)
**Date**: 2026-01-01

## Decision Table

| Topic | Decision | Rationale | Alternatives Considered |
|-------|----------|-----------|-------------------------|
| **Integration** | **Bin Wrapper** + **Composer Lib** | Using `bin/compile` wrapper maintains CLI convention while delegating to a clean package (`packages/compile`) allows us to use Composer autoloading and `crazywhalecc/static-php-cli` as a library. | `shell_exec` curl download of spc binary (harder to manage versioning). |
| **PHAR Tool** | **Custom Script** | We will implement a simple `PharCompiler` class instead of pulling in `humbug/box`. Why? Box is a heavy dependency and we need specific control over file exclusion that matches our config. | `humbug/box` (great but heavy), `phar-composer` (outdated). |
| **Config** | **Passed via Config/Args** | We will allow passing extra paths to the compilation process. Defaults: `src/`, `config/`, `public/`. | Hardcoded list (inflexible). |

## Integration Analysis

### Static PHP CLI (SPC) Usage
The library `crazywhalecc/static-php-cli` exposes internal classes like `SPC\builder\BuilderProvider`. However, it is primarily designed as a CLI tool.
**Risk**: Using internal classes might be unstable.
**Mitigation**: We will invoke the `spc` binary provided by the vendor package if possible, or use the `bin/spc` script it exposes.
**Actually**: The cleanest way is to use `vendor/bin/spc` if installed via composer.
*Correction*: `crazywhalecc/static-php-cli` is NOT designed to be used as a library easily. It is an application.
*Refined Strategy*: We will install it via composer dev-dependency, which puts the `spc` binary in `vendor/bin/spc`. Our `bin/compile` script will orchestrate:
1. Building our app PHAR.
2. Calling `vendor/bin/spc build --build-micro ...` passing our PHAR.

### Requirements for `spc`
- `spc` requires downloading sources (PHP, extensions).
- It handles this via `spc download`.
- Our script should check if downloads are needed.

## Conclusion
We will build a lightweight orchestration layer in `packages/compile` that wraps `Phar` creation and `spc` command execution.
