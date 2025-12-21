<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Integration;

use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function testFullScanAndDispatchFlow(): void
    {
        $router = new Router(new RouteRegistry());
        $router->scan(__DIR__ . '/../Fixtures');
        
        // Mock Request for GET /dummy/hello
        $request = $this->factory->createServerRequest('GET', '/dummy/hello');
        
        $result = $router->dispatch($request);
        
        $this->assertEquals('world', $result);
    }

    public function testRouteWithParameters(): void
    {
        // Define a dynamic route controller or manual register
        $router = new Router(new RouteRegistry());
        $router->register('GET', '/users/{id}', function ($id) {
            return "User: $id";
        });
        
        $request = $this->factory->createServerRequest('GET', '/users/42');
        $result = $router->dispatch($request);
        
        $this->assertEquals('User: 42', $result);
    }
}
