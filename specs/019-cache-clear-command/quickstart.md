# Quickstart: Cache Clear Command

## Usage

To clear all cached files and regenerate core bootstrap files:

```bash
bin/console cache:clear
```

## What it does

1. **Clears Content**: Deletes all files and folders within `var/cache/`.
2. **Warms Up**: Executes all registered `RegenerationListener` implementations to recreate critical files.
3. **Provides Feedback**: Lists which files were cleared and which listeners were successfully executed.

## Success Example

```text
! [NOTE] Clearing cache directory: /path/to/project/var/cache

 [OK] Cache cleared successfully.

! [NOTE] Warming up cache...
 - Regenerated: Provider Discovery
 - Regenerated: Dependency Injection Container

 [OK] Cache warmed up successfully.
```
