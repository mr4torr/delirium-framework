# Quickstart: Using PSR Interfaces

Delirium Framework encourages the use of standard PSR interfaces for method injection.

## Injecting the Request

To access the current HTTP request in your controller, type-hint `Psr\Http\Message\ServerRequestInterface`.

```php
use Psr\Http\Message\ServerRequestInterface;
use Delirium\Http\Attribute\Get;
use Delirium\Http\Attribute\Controller;

#[Controller('/example')]
class ExampleController
{
    #[Get('/request')]
    public function inspect(ServerRequestInterface $request): array
    {
        return [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $request->getHeaders(),
        ];
    }
}
```

## Injecting the Container

To access the Dependency Injection Container, type-hint `Psr\Container\ContainerInterface`.

**Note**: Usage of the Service Locator pattern (injecting the container) is generally discouraged in favor of injecting specific dependencies. Usage is permitted for factories or specific framework integrations.

```php
use Psr\Container\ContainerInterface;

class MyService
{
    public function __construct(private ContainerInterface $container) {}
}
```
