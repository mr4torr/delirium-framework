<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Unit\Invoker;

use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Invoker\ControllerInvoker;
use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ControllerInvokerTest extends TestCase
{
    private ControllerInvoker $invoker;
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->invoker = new ControllerInvoker();
        $this->factory = new Psr17Factory();
    }

    public function testInvokeCallable(): void
    {
        // Mock Response Chain
        $responseChain = $this->createMock(ResponseResolverChain::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $responseChain->expects($this->once())
            ->method('resolve')
            ->willReturn($mockResponse);

        $this->invoker->setResponseResolverChain($responseChain);

        $handler = fn() => 'hello';
        $this->invoker->invoke($handler, []);
    }

    public function testInvokeControllerMethod(): void
    {
        // Mock Container
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn(new class {
            public function action() { return 'action'; }
        });

        $this->invoker->setContainer($container);

        // Mock Request Chain
        $requestChain = $this->createMock(ArgumentResolverChain::class);
        $requestChain->method('resolveArguments')->willReturn([]);
        $this->invoker->setArgumentResolverChain($requestChain);

        // Mock Response Chain
        $responseChain = $this->createMock(ResponseResolverChain::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $responseChain->expects($this->once())
            ->method('resolve')
            ->willReturn($mockResponse);
        $this->invoker->setResponseResolverChain($responseChain);

        $request = $this->factory->createServerRequest('GET', '/');

        $this->invoker->invoke(['TestController', 'action'], [], $request);
    }
}
