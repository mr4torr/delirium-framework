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

    private function createConfiguredRouter(): Router
    {
        $registry = new RouteRegistry();
        $dispatcher = new \Delirium\Http\Dispatcher\RegexDispatcher();

        // Use Real Chain for integration
        $chain = new \Delirium\Http\Resolver\Response\ResponseResolverChain([
            new \Delirium\Http\Resolver\Response\JsonResolver($this->factory, $this->factory),
            new \Delirium\Http\Resolver\Response\DefaultValueResolver($this->factory, $this->factory),
        ]);
        $dispatcher->setResponseResolverChain($chain);

        // Also needs Request Chain
        $reqChain = new \Delirium\Http\Resolver\ArgumentResolverChain([
             new \Delirium\Http\Resolver\Request\RouteParameterResolver(),
             new \Delirium\Http\Resolver\Request\DefaultValueResolver(),
        ]);
        $dispatcher->setArgumentResolverChain($reqChain);

        $router = new Router($registry);
        $router->setDispatcher($dispatcher);

        return $router;
    }

    public function testFullScanAndDispatchFlow(): void
    {
        $router = $this->createConfiguredRouter();
        $router->scan(__DIR__ . '/../Fixtures');

        // Mock Request for GET /dummy/hello
        $request = $this->factory->createServerRequest('GET', '/dummy/hello');

        $response = $router->dispatch($request);

        // Assertion on response body. Default behavior (JsonResolver/Default) encodes strings as JSON string.
        $this->assertEquals('"world"', (string)$response->getBody());
    }

    public function testRouteWithParameters(): void
    {
        // Define a dynamic route controller or manual register
        $router = $this->createConfiguredRouter();
        $router->register('GET', '/users/{id}', function ($id) {
            return "User: $id";
        });

        $request = $this->factory->createServerRequest('GET', '/users/42');
        $response = $router->dispatch($request);

        $this->assertEquals('"User: 42"', (string)$response->getBody());
    }
}
