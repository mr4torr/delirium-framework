# Walkthrough: Console Runner

**Feature**: Console Runner (`012-console-runner`)
**Status**: Completed

## Changes

- **New Entry Point**: `bin/console` replacing `bin/server` and `bin/watcher`.
- **Core Commands**:
  - `server`: Starts the Swoole HTTP (Production).
  - `server:watch`: Starts the server with file watching (Development).
- **Architecture**:
  - `Delirium\Core\Console\Kernel` wrapping `Symfony\Component\Console\Application`.
  - Commands registered via Kernel.

## Verification

### 1. Listing Commands

Running `php bin/console list` shows available commands:

```text
Available commands:
  help        Display help for a command
  list        List commands
  server:start      Start the application server
  server:watch  Start server with live reload (Watcher)
```

### 2. Starting Server

```bash
php bin/console server:start --port=9501
```

Output:
```text
Starting server on http://0.0.0.0:9501
```

### 3. Development Watcher

```bash
php bin/console server:watch
```

Output:
```text
Starting Watcher...
Watching directories: src, packages
[Watcher] Started. Watching: src, packages
```

## Legacy Cleanup

- Removed `bin/server`.
- Removed `bin/watcher`.
