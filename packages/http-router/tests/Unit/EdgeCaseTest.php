<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Unit;

use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class EdgeCaseTest extends TestCase
{
    private $factory;
    private $router;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
        $this->router = new Router(new RouteRegistry());
    }

    public function testRouteNotFound(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionCode(404);

        $request = $this->factory->createServerRequest('GET', '/not-exist');
        $this->router->dispatch($request);
    }

    public function testMethodNotAllowed(): void
    {
        $this->router->register('GET', '/users', fn() => 'users');

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionCode(405);
        $this->expectExceptionMessage('Method POST not allowed');

        $request = $this->factory->createServerRequest('POST', '/users');
        $this->router->dispatch($request);
    }

    public function testDuplicateRouteThrowsException(): void
    {
        $this->router->register('GET', '/users', fn() => 'a');
        
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate route defined');

        $this->router->register('GET', '/users', fn() => 'b');
    }
}
