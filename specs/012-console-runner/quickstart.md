# Quickstart: Console Runner

## Overview

The `bin/console` script provides a centralized entry point for managing the Delirium application. It replaces standalone scripts like `bin/server` and `bin/watcher`.

## Usage

Run the console from the project root:

```bash
php bin/console [command] [options]
```

### Available Commands

- **`list`**: List all available commands.
- **`server:start`**: Start the Swoole HTTP server (Production mode).
- **`server:watch`**: Start the Swoole HTTP server with file watching (Development mode).

### Examples

**Start the server:**

```bash
php bin/console server:start
```

**Start with live reload:**

```bash
php bin/console server:watch
```

**Check version:**

```bash
php bin/console --version
```
