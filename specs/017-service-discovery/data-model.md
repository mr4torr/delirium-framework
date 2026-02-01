# Data Model: Service Discovery

## 1. Entities

### ServiceProvider (Abstract)
The configuration object for a package/module.
- **Contract**: `Delirium\Support\ServiceProvider`
- **Methods**:
    - `register(ContainerBuilder $container)`: Add definitions.
    - `boot()`: Initialize services.

### AliasMap
A simple key-value structure managed by the `AliasLoader`.
- **Key**: Short name (e.g., `Route`).
- **Value**: FQN (e.g., `Delirium\Http\Router`).

## 2. Cache Structure

The file `var/cache/discovery.php` will return a PHP array:

```php
<?php

return [
    'providers' => [
        'all' => [
            'Delirium\Http\HttpServiceProvider',
            'Delirium\DI\DIServiceProvider',
        ],
        'dev' => [
            'Delirium\DevTools\DevToolsServiceProvider',
        ],
        'prod' => [],
    ],
    'aliases' => [
        'Route' => 'Delirium\Http\Router',
        'App' => 'Delirium\Core\Application',
    ],
];
```

## 3. Lifecycle Sequence

1. **Instantiation**: `Application` is created.
2. **Registration**:
   - Code calls `$app->register(Provider::class)`.
   - `ProviderRepository` collects the class name and environment.
3. **Booting** (`$app->listen()` or `$app->boot()`):
   - Load Cache if valid.
   - For each Provider in `current_env` + `all`:
     - Instantiate.
     - Call `register()`.
   - For each Alias:
     - Register via `class_alias()`.
   - For each Provider:
     - Call `boot()`.
4. **Persistence**: If cache was missing, write the collected list to `var/cache/discovery.php`.
