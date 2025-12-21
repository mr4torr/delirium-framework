---
trigger: always_on
---

# delirium-framework Development Guidelines

Auto-generated from all feature plans. Last updated: 2025-12-20

## Active Technologies
- PHP 8.4+ + `symfony/dependency-injection`, `symfony/config` (003-dependency-injection)
- File system for cached container (`var/cache/`) (003-dependency-injection)
- PHP 8.4 (CLI) + None (Standard Library) or `Swoole\Process` if beneficial. (005-live-reload)
- PHP 8.4 (CLI) + None (Standard Library) or `Swoole\Process` if beneficial. (005-live-reload)
- PHP 8.4+ + `symfony/dependency-injection`, `nyholm/psr7`, `psr/container`, `psr/http-message` (007-use-psr-interfaces)
- PHP 8.4+ + `symfony/dependency-injection`, `nyholm/psr7` (Standard PSRs are preferred) (008-interface-abstraction)

- PHP 8.4 + openswoole/core, psr/http-message, psr/container (001-http-routing-pkg)

## Project Structure

```text
src/
tests/
```

## Commands

# Add commands for PHP 8.4

## Code Style

PHP 8.4: Follow standard conventions

## Recent Changes
- 008-interface-abstraction: Added PHP 8.4+ + `symfony/dependency-injection`, `nyholm/psr7` (Standard PSRs are preferred)
- 007-use-psr-interfaces: Added PHP 8.4+ + `symfony/dependency-injection`, `nyholm/psr7`, `psr/container`, `psr/http-message`
- 005-live-reload: Added PHP 8.4 (CLI) + None (Standard Library) or `Swoole\Process` if beneficial.


<!-- MANUAL ADDITIONS START -->
Always open `@/.specify/memory/constitution.md` when the request:

Use `@/.specify/memory/constitution.md` to learn:

<!-- MANUAL ADDITIONS END -->
