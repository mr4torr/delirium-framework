<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Integration;

use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Delirium\Http\Attribute\Get;
use Delirium\Http\Contract\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class AttributeStatusOverrideTest extends TestCase
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

        $psr17Factory = $this->factory;

        // Response Chain
        $chain = new \Delirium\Http\Resolver\Response\ResponseResolverChain([
            new \Delirium\Http\Resolver\Response\JsonResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\DefaultValueResolver($psr17Factory, $psr17Factory),
        ]);
        $dispatcher->setResponseResolverChain($chain);

        // Request Chain - With Fixed ResponseResolver
        $reqChain = new \Delirium\Http\Resolver\ArgumentResolverChain([
             new \Delirium\Http\Resolver\Request\ResponseResolver($psr17Factory, $psr17Factory),
        ]);
        $dispatcher->setArgumentResolverChain($reqChain);

        $router = new Router($registry);
        $router->setDispatcher($dispatcher);

        return $router;
    }

    public function testAttributeStatusIsUsedByDefault(): void
    {
        $router = $this->createConfiguredRouter();

        // Register controller instance manually to avoid container complexity in test
        $controller = new class {
            #[Get(path: '/default', status: 201)]
            public function index(ResponseInterface $response) {
                return $response;
            }

            #[Get(path: '/override', status: 201, type: \Delirium\Http\Enum\ResponseTypeEnum::JSON)]
            public function override(ResponseInterface $response) {
                 return $response->withStatus(400)->withHeader('Content-Type', 'text/plain');
            }
        };

        // We need to verify dispatch logic.
        // Our test router registration usually uses 'Class@method'.
        // RegexDispatcher supports array [instance, method].
        // We need to bypass Router::register string parsing or implement manual route add.

        // We can hack it by adding route to dispatcher directly if we had access,
        // or just use Router->register with callable?
        // But attributes are on the method.
        // The Dispatcher uses Reflection on the handler.

        // Let's rely on standard Router scan? Or manual register with array handler.
        // Router->register allows handler mixed.
        $router->register('GET', '/default', [$controller, 'index']);
        $router->register('GET', '/override', [$controller, 'override']);

        // Test Default
        $request = $this->factory->createServerRequest('GET', '/default');
        $response = $router->dispatch($request);
        $this->assertEquals(201, $response->getStatusCode(), 'Status should be 201 from attribute');
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'), 'Content-Type should be application/json from attribute default');

        // Test Override
        $request = $this->factory->createServerRequest('GET', '/override');
        $response = $router->dispatch($request);
        $this->assertEquals(400, $response->getStatusCode(), 'Status should be 400 from controller override');
        $this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'), 'Content-Type should be text/plain from controller override');
    }
}
