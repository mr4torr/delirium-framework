# Data Model & Interfaces

## Attributes (Meta-Data)

### RouteAttribute (Abstract)
- `string $path`: The URL path pattern.
- `array $methods`: HTTP methods supported.

### Http Method Attributes (Extends RouteAttribute)
- `Get`: methods=['GET']
- `Post`: methods=['POST']
- `Put`: methods=['PUT']
- `Delete`: methods=['DELETE']
- `Patch`: methods=['PATCH']
- `Options`: methods=['OPTIONS']
- `Head`: methods=['HEAD']

### Controller
- `string $prefix`: Global route prefix for the class.

## Core Interfaces

### RouterInterface
- `scan(string $directory): void`: Scans for attributes.
- `register(string $method, string $path, callable|array $handler): void`: Manually register route.
- `dispatch(ServerRequestInterface $request): mixed`: Executed matched handler.

### ContextAdapterInterface
- `createFromSwoole(Swoole\Http\Request $swooleRequest): ServerRequestInterface`: Factory method.
- `emitToSwoole(ResponseInterface $psrResponse, Swoole\Http\Response $swooleResponse): void`: Emitter.
