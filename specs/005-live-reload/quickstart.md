# Quickstart: Live Reload Watcher

This guide explains how to use the new Live Reload watcher to speed up development.

## Usage

From the project root, run:

```bash
php bin/watcher
```

## How it works

1.  The watcher starts the underlying web server (e.g., `php public/index.php`).
2.  It monitors `src/` and `packages/` for any changes to PHP files.
3.  When a file is saved, it automatically restarts the server.

## Configuration

You can configure the Live Reload feature using the `DebugOptions` class in `AppFactory`.

```php
use Delirium\Core\AppOptions;
use Delirium\Core\Options\DebugOptions;

$options = new AppOptions(
    new DebugOptions(
        debug: true,
        liveReload: true, // Enable watcher
        watchDirs: ['src', 'packages', 'config'] // Custom directories
    )
);
```

Defaults:
- **liveReload**: `false`
- **watchDirs**: `['src', 'packages']`
