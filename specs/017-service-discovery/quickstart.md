# Quickstart: Service Discovery

## 1. Creating a Service Provider

Extend `Delirium\Support\ServiceProvider` and implement the `register` method.

```php
namespace App\Providers;

use Delirium\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services to the container
        // $this->container->register(MyService::class);
    }

    public function boot(): void
    {
        // Logic to run after all providers are registered
    }
}
```

## 2. Registering Providers & Aliases

In your application entry point (typically `src/AppFactory.php` or `bin/console`):

```php
$app = AppFactory::create();

// Register Service Providers
$app->register(\App\Providers\AppServiceProvider::class);

// Register DevTools only in 'dev' environment
$app->register(\Delirium\DevTools\DevToolsServiceProvider::class, 'dev');

// Register Class Aliases
$app->alias('Route', \Delirium\Http\Router::class);
$app->alias('Config', \Delirium\Support\Config::class);

$app->listen();
```

## 3. Using Aliases

Once registered, you can use the short name anywhere without the full namespace:

```php
use Route; // Optional if you want to be explicit, but it works globally.

Route::get('/', function() {
    return 'Hello World';
});
```

## 4. Performance

To enable caching in production, ensure the `var/cache` directory is writable. The framework will automatically generate `var/cache/discovery.php` which bypasses the registration logic overhead.

To clear the cache:
```bash
rm -rf var/cache/*
```

## 4. Running

Dev Environment:

✅ server:watch  Start server with live reload (Watcher)

```bash
APP_ENV=dev php bin/console list

```

Production (default):

```bash
php bin/console list
```