# Quickstart: Decoupling & Architecture

This guide explains how to work with the new decoupled architecture, specifically usage of the `Support` package and the `Deptrac` tool.

## 1. Using the Support Package

The `Delirium\Support` package contains zero-dependency utilities.

### Installation
In your package's `composer.json`:
```json
"require": {
    "delirium/support": "*"
}
```
*Note: In the monorepo, version is usually `dev-main` or `*`.*

### Usage
```php
use Delirium\Support\Str;

$random = Str::random(32);
$has = Str::contains("hello world", "world");
```

## 2. Running Architectural Checks

We use **Deptrac** to enforce that packages do not inadvertently depend on Core or each other.

### Installation (Root)
Deptrac is installed as a development dependency.
```bash
composer require --dev deptrac/deptrac
```

### Running Checks
To scan the codebase for violations:
```bash
vendor/bin/deptrac analyze
```
Or via the shortcut (if configured in composer.json):
```bash
composer arch/deptrac
```

### Reading Output
Green means compliance. Red indicates a violation:
```text
LayerDependency ==> Core depends on Http (Allowed)
LayerDependency ==> Http depends on Core (VIOLATION)
  /src/Router/RegexDispatcher.php:45 matches "Delirium\Core\Container"
```
**Fix**: Refactor the code to remove the dependency (e.g., inject an interface instead).

## 3. Adding New Packages

When adding a new package `packages/new-thing`:
1. Add it to `depfile.yaml` under `layers`.
2. Define its `ruleset` (what allowed dependencies it has).
3. Ensure it relies on `Support` rather than duplicating helpers.
