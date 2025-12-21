<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Http;

use Delirium\Core\Http\Response;
use Nyholm\Psr7\Response as Psr7Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $response = new Response();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(Psr7Response::class, $response);
    }

    public function testConstructorCompatibleWithPsr7(): void
    {
        $response = new Response(404, ['X-Test' => '1'], 'Not Found');
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('1', $response->getHeaderLine('X-Test'));
        $this->assertSame('Not Found', (string) $response->getBody());
    }

    public function testBodyMethodWithArraySerializesToJson(): void
    {
        $response = new Response();
        $data = ['status' => 'ok', 'id' => 123];
        
        $newResponse = $response->body($data);

        $this->assertNotSame($response, $newResponse, 'body() should return a new instance (immutability)');
        $this->assertSame(200, $newResponse->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string) $newResponse->getBody());
        $this->assertSame('application/json', $newResponse->getHeaderLine('Content-Type'));
    }

    public function testBodyMethodWithString(): void
    {
        $response = new Response();
        $newResponse = $response->body('simple string');

        $this->assertSame('simple string', (string) $newResponse->getBody());
        // Should NOT strictly enforce content-type for string unless we want to default to text/html?
        // Spec says: "string: set as is".
    }

    public function testBodyMethodWithInteger(): void
    {
        $response = new Response();
        $newResponse = $response->body(100);

        $this->assertSame('100', (string) $newResponse->getBody());
    }
}
