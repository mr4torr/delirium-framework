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
- PHP 8.4 + `ext-swoole` (replacing `openswoole/core`), `swoole/ide-helper` (dev) (010-switch-to-swoole)
- PHP 8.4 + `symfony/console`, `ext-swoole`, `symfony/dependency-injection` (012-console-runner)

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
- 012-console-runner: Added PHP 8.4 + `symfony/console`, `ext-swoole`, `symfony/dependency-injection`
- 011-spc-compiler: Added [if applicable, e.g., PostgreSQL, CoreData, files or N/A]
- 010-switch-to-swoole: Added PHP 8.4 + `ext-swoole` (replacing `openswoole/core`), `swoole/ide-helper` (dev)


<!-- MANUAL ADDITIONS START -->
Always open `@/.specify/memory/constitution.md` when the request:

Use `@/.specify/memory/constitution.md` to learn:

<!-- MANUAL ADDITIONS END -->
