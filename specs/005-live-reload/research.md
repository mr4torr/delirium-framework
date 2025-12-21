# Research: Live Reload

**Feature**: Live Reload (005)

## Decisions

### 1. File Monitoring Strategy
**Decision**: Pure PHP Polling with `RecursiveDirectoryIterator`.
**Rationale**:
- **Portability**: Does not require installing PECL extensions (`inotify`) or external binaries (`watchman`, `chokidar`).
- **Simplicity**: Easy to implement and debug in PHP.
- **Performance**: For development environments with < 10,000 files, a 1-second polling interval is negligible in terms of CPU usage.

**Alternatives Considered**:
- **inotify (PECL)**: Fastest, event-driven. Rejected because it requires compilation/extensions which raises the barrier to entry for contributors.
- **External Binaries**: Node.js tools are great but introduce a Node dependency in a PHP-first project.

### 2. Process Management
**Decision**: Use `proc_open` to spawn the child process.
**Rationale**:
- Allows real-time piping of `stdout` and `stderr` from the server to the watcher's terminal.
- Provides a resource handle to send signals (`SIGTERM`) for graceful shutdown.

**Alternatives Considered**:
- `exec`/`passthru`: Blocking, cannot easily kill the child.
- `pcntl_fork`: More complex memory management (copy-on-write), and running the server inside the forked process might complicate signal handling if not careful. `proc_open` isolates the server process better.

### 3. Architecture
**Decision**: New package `packages/dev-tools`.
**Rationale**:
- Keeps the core framework (`packages/core`) lean.
- Development tools shouldn't be deployed to production.
- Allows separate versioning/dependencies if needed.
