<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Integration;

use Delirium\Http\Attribute\Get;
use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Dispatcher\RegexDispatcher;
use Delirium\Http\Enum\ResponseTypeEnum;
use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Request\ResponseResolver;
use Delirium\Http\Resolver\Response\DefaultValueResolver;
use Delirium\Http\Resolver\Response\JsonResolver;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
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
        $dispatcher = new RegexDispatcher();

        $psr17Factory = $this->factory;

        // Response Chain
        $chain = new ResponseResolverChain([
            new JsonResolver($psr17Factory, $psr17Factory),
            new DefaultValueResolver($psr17Factory, $psr17Factory),
        ]);
        $dispatcher->setResponseResolverChain($chain);

        // Request Chain - With Fixed ResponseResolver
        $reqChain = new ArgumentResolverChain([
             new ResponseResolver($psr17Factory, $psr17Factory),
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

            #[Get(path: '/override', status: 201, type: ResponseTypeEnum::JSON)]
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
