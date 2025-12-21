<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class PsrInjectionTest extends TestCase
{
    public function testPsrInjectionController()
    {
        // Simulate a request to the controller
        // Ideally we use a test client if available, or just verify the controller logic via unit test if full app boot is heavy.
        // Given this is a framework test in `tests/`, we assume we can boot the app or use HTTP client.
        // For simplicity and speed, let's use cURL or just check if the class exists and is reachable if the server was running.
        // But since we are in `tests/`, let's assume we can run a quick functional test.

        // Real integration test via internal dispatch (if supported):
        // $response = $app->handle(new ServerRequest('GET', '/psr-test'));
        
        // Since I don't see a `TestClient` in the file list easily, I'll write a simple test that instantiates the controller manually 
        // with mocks to verify the signature works as expected, satisfying "Independent Test" criteria of verification.
        
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');

        $controller = new \App\Example\PsrInjectionController($container);
        // Inject a Response object as the second argument, as required by the modified signature
        $response = $controller->index($request, new \Delirium\Core\Http\Response());

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('PSR Injection Works!', (string) $response->getBody());
    }
}
