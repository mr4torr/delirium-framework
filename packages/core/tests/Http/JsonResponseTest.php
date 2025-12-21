<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Http;

use Delirium\Core\Http\JsonResponse;
use Delirium\Core\Http\Response; // Inherits from our Response?
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    public function testConstructorSetsHeaderAndBody(): void
    {
        $data = ['foo' => 'bar'];
        $response = new JsonResponse(201, [], $data);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string) $response->getBody());
    }

    public function testConstructorMergesHeaders(): void
    {
        $response = new JsonResponse(200, ['X-Custom' => 'Value'], ['a' => 1]);

        $this->assertSame('Value', $response->getHeaderLine('X-Custom'));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testInheritance(): void
    {
        // JsonResponse should likely extend Response for consistency, or directly PSR-7 but with changed constructor.
        // Given spec says "Specialized class", usually it extends Response or Base.
        $this->assertInstanceOf(Response::class, new JsonResponse(200, [], [])); 
    }
}
