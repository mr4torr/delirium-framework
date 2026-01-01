# Research: Switch to Swoole

**Feature**: Switch to Swoole (010-switch-to-swoole)
**Date**: 2026-01-01

## Decision Table

| Topic | Decision | Rationale | Alternatives Considered |
|-------|----------|-----------|-------------------------|
| **Engine** | **Upstream Swoole (`ext-swoole`)** | Required for Static PHP CLI (SPC) compatibility and wider ecosystem support. | OpenSwoole (Current), ReactPHP (Different paradigm), Amphp (Different paradigm) |
| **Namespace** | **`Swoole\*`** | Standard namespace for upstream Swoole unless "Short Names" are enabled. We will use FQCN `Swoole\Http\Server` etc. for clarity. | Short names (`\swoole_http_server`) - legacy style. |
| **Dependency** | **`ext-swoole`** | We will require the extension directly in `composer.json` to ensure the runtime environment is correct. | `swoole/swoole-src` (This is the source repo, not a composer package for the runtime). |

## Migration Analysis

### Class Mapping

We need to map the following `OpenSwoole` classes to `Swoole`:

| OpenSwoole | Swoole | Notes |
|------------|--------|-------|
| `OpenSwoole\Http\Server` | `Swoole\Http\Server` | Constructor signature is identical `($host, $port)`. |
| `OpenSwoole\Http\Request` | `Swoole\Http\Request` | Properties `server`, `header`, `get`, `post` are identical key-value stores. |
| `OpenSwoole\Http\Response` | `Swoole\Http\Response` | Methods `end()`, `status()`, `header()` are identical. |

### Configuration Changes

- **`composer.json`**:
  - Remove: `openswoole/core`
  - Add: `ext-swoole: *` (or specific version like `^5.0`)
  - Dev: Add `swoole/ide-helper` for code completion.

### Potential Risks

1. **Short Names**: Swoole allows disabling short names (`swoole.use_shortname = Off`). We should use Namespaced classes to be safe, but upstream Swoole historically used short names (snake_case) a lot. Modern versions support `Swoole\` namespace.
2. **Context**: We use `Swoole\Context` (implied in Constitution). OpenSwoole has `OpenSwoole\Context`? We need to ensure we use `Swoole\Context` if we rely on coroutine context storage.
   - *Self-correction*: We are not explicitly using `Context` class in the inspected files yet, but if we do, it must be updated.

## Conclusion

The refactor is straightforward text-replacement for the namespaces, provided we update the `composer` dependencies first to allow the code to run in the CI/Test environment (which obviously needs `ext-swoole` installed).
