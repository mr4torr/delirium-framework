<?php

declare(strict_types=1);

namespace Tests\Feature;

use Delirium\Core\Http\JsonResponse;
use Delirium\Core\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseClassTest extends TestCase
{
    public function testResponseClassUsage(): void
    {
        // Simulate usage in a controller
        $data = ['id' => 1, 'name' => 'Delirium'];
        
        // Scenario 1: Using base Response with body()
        $response = (new Response())->body($data);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string) $response->getBody());
    }

    public function testJsonResponseClassUsage(): void
    {
        // Scenario 2: Using JsonResponse directly
        $data = ['error' => 'not_found'];
        $response = new JsonResponse(404, $data);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertJsonStringEqualsJsonString(json_encode($data), (string) $response->getBody());
    }
}
