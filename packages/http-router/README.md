# Delirium HTTP Router

Attribute-based HTTP Router for Delirium Framework.
Powered by PHP 8 Attributes, Swoole, and PSR-7.

## Installation

```bash
composer require delirium/http-router
```

## Usage

### Define Controllers

Annotate your controller classes with `#[Controller]` and methods with `#[Get]`, `#[Post]`, etc.

```php
use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;

#[Controller('/users')]
class UserController
{
    #[Get('/{id}')]
    public function show(string $id)
    {
        return "User ID: $id";
    }
}
```

### Setup Router

```php
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;

$router = new Router(new RouteRegistry());
$router->scan(__DIR__ . '/Controllers'); // Scans directory for attributes

// Dispatch a PSR-7 Request
$response = $router->dispatch($psr7Request);
```

### Swoole Bridge

Use `SwoolePsrAdapter` to convert Swoole requests to PSR-7.

```php
use Delirium\Http\Bridge\SwoolePsrAdapter;
use Delirium\Http\RouteRegistry;

$http = new Swoole\Http\Server('127.0.0.1', 9501);
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$adapter = new SwoolePsrAdapter($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

$router = new Router(new RouteRegistry());
$router->scan(__DIR__ . '/src');

$http->on('request', function ($swooleRequest, $swooleResponse) use ($adapter, $router) {
    try {
        $psrRequest = $adapter->createFromSwoole($swooleRequest);
        $result = $router->dispatch($psrRequest);
        
        // Handle result (convert string/array to PSR-7 Response if needed)
        // Ideally result is ResponseInterface.
        // For simple string returns:
        $psrResponse = new \Nyholm\Psr7\Response(200, [], (string)$result);
        
        $adapter->emitToSwoole($psrResponse, $swooleResponse);
    } catch (\Throwable $e) {
        $swooleResponse->status(500);
        $swooleResponse->end($e->getMessage());
    }
});

$http->start();
```

## Features

- PHP 8 Attribute Routing (`#[Get]`, `#[Post]`, etc.)
- Dynamic Route Parameters (`/users/{id}`)
- Swoole <-> PSR-7 Bridge
- Regex-based Dispatcher
