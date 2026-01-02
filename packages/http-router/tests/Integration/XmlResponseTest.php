<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Integration;

use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use Delirium\Http\Attribute\Get;
use Delirium\Http\Contract\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class XmlResponseTest extends TestCase
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

        // Response Chain - Order matters, mirroring AppFactory
        $chain = new \Delirium\Http\Resolver\Response\ResponseResolverChain([
            new \Delirium\Http\Resolver\Response\JsonResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\XmlResolver($psr17Factory, $psr17Factory),
            new \Delirium\Http\Resolver\Response\DefaultValueResolver($psr17Factory, $psr17Factory),
        ]);
        $dispatcher->setResponseResolverChain($chain);

        // Request Chain
        $reqChain = new \Delirium\Http\Resolver\ArgumentResolverChain([
             new \Delirium\Http\Resolver\Request\DefaultValueResolver(),
        ]);
        $dispatcher->setArgumentResolverChain($reqChain);

        $router = new Router($registry);
        $router->setDispatcher($dispatcher);

        return $router;
    }

    public function testXmlResponseFromArray(): void
    {
        $router = $this->createConfiguredRouter();

        $controller = new class {
            #[Get(path: '/xmltest', type: \Delirium\Http\Enum\ResponseTypeEnum::XML, status: 201)]
            public function index(): array {
                return [
                    'home' => 'Olá Mundo',
                ];
            }
        };

        $router->register('GET', '/xmltest', [$controller, 'index']);

        $request = $this->factory->createServerRequest('GET', '/xmltest');
        $response = $router->dispatch($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        // Expect XML, not JSON
        $this->assertStringStartsWith('<?xml', $body);

        // Parse XML to assert value, avoiding encoding mismatch issues (e.g. Olá vs Ol&#xE1;)
        $xml = new \SimpleXMLElement($body);
        $this->assertEquals('Olá Mundo', (string)$xml->home);
    }
}
