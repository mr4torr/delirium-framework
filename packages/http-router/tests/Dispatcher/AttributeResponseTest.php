<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Dispatcher;

use Attribute;
use Delirium\Http\Dispatcher\RegexDispatcher;
use Delirium\Http\Enum\ResponseTypeEnum;
use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Delirium\Http\Resolver\Response\JsonResolver;
use Delirium\Http\Resolver\Response\DefaultValueResolver;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

#[Attribute]
class RouteStub {
    public function __construct(public mixed $type = '', public int $status = 200) {}
}

class ControllerStub {
    #[RouteStub(type: ResponseTypeEnum::JSON)]
    public function jsonParams() {
        return ['foo' => 'bar'];
    }

    #[RouteStub(status: 201)]
    public function createdStatus() {
        return 'created';
    }
}

class AttributeResponseTest extends TestCase
{
    private $dispatcher;
    private $responseChain;

    protected function setUp(): void
    {
        $this->dispatcher = new RegexDispatcher();

        // Use Real Factories if available for integration test
        if (class_exists(Psr17Factory::class)) {
            $factory = new Psr17Factory();

            $this->responseChain = new ResponseResolverChain([
                new JsonResolver($factory, $factory),
                new DefaultValueResolver($factory, $factory)
            ]);
        } else {
             // Mock fallback if Nyholm absent (unlikely per deps)
             $this->markTestSkipped('Nyholm PSR-7 Factory not found');
        }

        $this->dispatcher->setResponseResolverChain($this->responseChain);

        // Mock Request chain stub
        $reqChain = $this->createMock(ArgumentResolverChain::class);
        $reqChain->method('resolveArguments')->willReturn([]);
        $this->dispatcher->setArgumentResolverChain($reqChain);
    }

    public function testJsonAttribute()
    {
        $factory = new Psr17Factory();

        $this->dispatcher->addRoute('GET', '/json', [ControllerStub::class, 'jsonParams']);

        $uri = $factory->createUri('/json');
        $realRequest = $factory->createServerRequest('GET', $uri);

        $response = $this->dispatcher->dispatch($realRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('Content-Type'));
        $this->assertJsonStringEqualsJsonString('{"foo":"bar"}', (string)$response->getBody());
    }

    public function testStatusAttribute()
    {
        $factory = new Psr17Factory();

        $this->dispatcher->addRoute('POST', '/created', [ControllerStub::class, 'createdStatus']);

        $uri = $factory->createUri('/created');
        $realRequest = $factory->createServerRequest('POST', $uri);

        $response = $this->dispatcher->dispatch($realRequest);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('created', (string)$response->getBody());
    }
}
