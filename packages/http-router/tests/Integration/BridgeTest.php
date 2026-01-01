<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Integration;

use Delirium\Http\Bridge\SwoolePsrAdapter;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Response;

class BridgeTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists('Swoole\Http\Request')) {
            $this->markTestSkipped('Swoole extension or polyfill not available.');
        }
    }

    public function testCreateFromSwoole(): void
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $adapter = new SwoolePsrAdapter($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        
        $swooleRequest = $this->createMock(SwooleRequest::class);
        $swooleRequest->server = [
            'request_method' => 'POST',
            'request_uri' => '/test',
            'server_protocol' => 'HTTP/1.1',
            'server_port' => 8080
        ];
        $swooleRequest->header = [
            'host' => 'localhost',
            'content-type' => 'application/json'
        ];
        $swooleRequest->get = ['q' => 'foo'];
        $swooleRequest->post = ['data' => 'bar'];
        $swooleRequest->method('getContent')->willReturn('{"json":"body"}');

        $psrRequest = $adapter->createFromSwoole($swooleRequest);

        $this->assertEquals('POST', $psrRequest->getMethod());
        $this->assertEquals('/test', $psrRequest->getUri()->getPath());
        $this->assertEquals('foo', $psrRequest->getQueryParams()['q']);
        $this->assertEquals('bar', $psrRequest->getParsedBody()['data']);
        $this->assertEquals('{"json":"body"}', (string) $psrRequest->getBody());
        $this->assertEquals('application/json', $psrRequest->getHeaderLine('content-type'));
    }

    public function testEmitToSwoole(): void
    {
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $adapter = new SwoolePsrAdapter($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        
        $swooleResponse = $this->createMock(SwooleResponse::class);
        $swooleResponse->expects($this->once())->method('status')->with(201, 'Created');
        $swooleResponse->expects($this->exactly(1))->method('header')->with('X-Test', 'Value');
        $swooleResponse->expects($this->once())->method('end')->with('Response Body');

        $psrResponse = new Response(201, ['X-Test' => 'Value'], 'Response Body');

        $adapter->emitToSwoole($psrResponse, $swooleResponse);
    }
}
