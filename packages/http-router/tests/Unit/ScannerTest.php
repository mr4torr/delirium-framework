<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Unit;

use Delirium\Http\RouteRegistry;
use Delirium\Http\Scanner\AttributeScanner;
use PHPUnit\Framework\TestCase;

class ScannerTest extends TestCase
{
    public function testScanDirectoryRegistersRoutes(): void
    {
        $registry = new RouteRegistry();
        $scanner = new AttributeScanner($registry);

        $scanner->scanDirectory(__DIR__ . '/../Fixtures');
        
        $routes = $registry->getRoutes();
        
        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/dummy/hello', $routes['GET']);
        $this->assertEquals(
            ['Delirium\Http\Tests\Fixtures\DummyController', 'hello'], 
            $routes['GET']['/dummy/hello']
        );

        $this->assertArrayHasKey('POST', $routes);
        $this->assertArrayHasKey('/dummy/create', $routes['POST']);
    }
}
