<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Example\ExampleController;
use App\Example\GreetingService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class ExampleControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $mockService = $this->createMock(GreetingService::class);
        $mockService->method('greet')->with('World é nois')->willReturn('Hello World é nois');

        $controller = new ExampleController($mockService);
        $this->assertEquals('Hello World é nois', $controller->index());
    }

    public function testMethodInjection(): void
    {
        $mockService = $this->createMock(GreetingService::class);
        $mockService->method('greet')->with('Antigravity')->willReturn('Hello Antigravity');

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->method('getPath')->willReturn('/inject/Antigravity');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getUri')->willReturn($mockUri);

        $controller = new ExampleController($mockService);
        $result = $controller->methodInjection('Antigravity', $mockService, $mockRequest);

        $this->assertEquals('Method Injection: Hello Antigravity URL: /inject/Antigravity', $result);
    }

    public function testContentArray(): void
    {
        $mockService = $this->createMock(GreetingService::class);
        $mockService->method('greet')->with('JSON')->willReturn('Hello JSON');

        $mockUri = $this->createMock(UriInterface::class);
        $mockUri->method('getPath')->willReturn('/array/JSON');

        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->method('getUri')->willReturn($mockUri);
        $mockRequest->method('getQueryParams')->willReturn(['foo' => 'bar']);

        $controller = new ExampleController($mockService);
        $result = $controller->contentArray('JSON', $mockService, $mockRequest);

        $this->assertIsArray($result);
        $this->assertEquals('JSON', $result['name']);
        $this->assertEquals('Hello JSON', $result['greeting']);
        $this->assertEquals('/array/JSON', $result['url']);
        $this->assertEquals(['foo' => 'bar'], $result['params']);
    }
}
