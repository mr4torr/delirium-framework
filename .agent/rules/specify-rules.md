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
- PHP 8.4+ + `nyholm/psr7` (Core), `psr/http-message`, `psr/container` (013-psr7-support)
- PHP 8.4 + Composer (autoload PSR-4), existing codebase dependencies (014-refactor-inline-imports)
- N/A (Code refactoring only) (014-refactor-inline-imports)
- PHP 8.4+ + `qossmic/deptrac`, `delirium/core`, `delirium/http-router`, `delirium/dependency-injection`, `delirium/validation`, `delirium/support` (new). (016-decouple-packages)
- File system for `depfile.yaml` cache/config. (016-decouple-packages)
- PHP 8.4+ (utilizing strict types and possibly property hooks if applicable) + `delirium/support`, `delirium/core`, `psr/container` (017-service-discovery)
- File-based cache in `var/cache/discovery.php` (017-service-discovery)
- PHP 8.4 + `symfony/console`, `delirium/http-router` (018-route-list-command)
- N/A (Read-only from memory/registry) (018-route-list-command)

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
- 018-route-list-command: Added PHP 8.4 + `symfony/console`, `delirium/http-router`
- 017-service-discovery: Added PHP 8.4+ (utilizing strict types and possibly property hooks if applicable) + `delirium/support`, `delirium/core`, `psr/container`
- 016-decouple-packages: Added PHP 8.4+ + `qossmic/deptrac`, `delirium/core`, `delirium/http-router`, `delirium/dependency-injection`, `delirium/validation`, `delirium/support` (new).


<!-- MANUAL ADDITIONS START -->
Always open `@/.specify/memory/constitution.md` when the request:

Use `@/.specify/memory/constitution.md` to learn:

<!-- MANUAL ADDITIONS END -->
