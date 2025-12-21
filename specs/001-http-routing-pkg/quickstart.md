# Quickstart: HTTP Routing Package

## Installation

```bash
composer require delirium/http-router
```

## Basic Usage

### 1. Define a Controller

```php
use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller('/users')]
class UserController
{
    #[Get('/{id}')]
    public function profile(int $id, ServerRequestInterface $req): ResponseInterface
    {
        return new Response(200, [], "User Profile: $id");
    }
}
```

### 2. Boot the Router in OpenSwoole

```php
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Swoole\Http\Server;

$router = new Router(new RouteRegistry());
$router->scan(__DIR__ . '/Controllers'); // Scans classes

$server = new Server("0.0.0.0", 9501);

$server->on("request", function ($req, $res) use ($router) {
    // 1. Convert to PSR-7 (Handled internally or via Adapter)
    // 2. Dispatch
    $response = $router->handle($req, $res);
    
    // 3. Emit (if not handled by router->handle auto-emit)
});

$server->start();
```
