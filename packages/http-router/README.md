# Delirium HTTP Router

Attribute-based HTTP Router for Delirium Framework.
Powered by PHP 8 Attributes, OpenSwoole, and PSR-7.

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

$router = new Router();
$router->scan(__DIR__ . '/Controllers'); // Scans directory for attributes

// Dispatch a PSR-7 Request
$response = $router->dispatch($psr7Request);
```

### OpenSwoole Bridge

Use `SwoolePsrAdapter` to convert OpenSwoole requests to PSR-7.

```php
use Delirium\Http\Bridge\SwoolePsrAdapter;

$http = new OpenSwoole\Http\Server('127.0.0.1', 9501);
$adapter = new SwoolePsrAdapter();
$router = new Router();
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
- OpenSwoole <-> PSR-7 Bridge
- Regex-based Dispatcher
