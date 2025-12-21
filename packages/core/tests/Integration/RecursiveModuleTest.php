<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Integration;

use Delirium\Core\AppFactory;
use Delirium\Core\Tests\Fixtures\Hierarchy\RootModule;
use Delirium\Http\Contract\RouterInterface;
use PHPUnit\Framework\TestCase;

class RecursiveModuleTest extends TestCase
{
    public function testRecursiveModuleScanning(): void
    {
        $app = AppFactory::create(RootModule::class);
        $container = $app->getContainer();
        
        $this->assertTrue($container->has(RouterInterface::class), 'Router should be registered in container');
        
        /** @var RouterInterface $router */
        $router = $container->get(RouterInterface::class);
        
        // We can inspect the registry if we cast to Router (implementation detail test)
        // Or we can mock a request and attempt dispatch?
        // Since Dispatcher is lazy loaded in Router implementation, maybe dispatching is safer check.
        
        // However, Router implementation is:
        // class Router implements RouterInterface { ... public function getRegistry() ... }
        
        if ($router instanceof \Delirium\Http\Router) {
            $registry = $router->getRegistry();
            $routes = $registry->getRoutes();
            
            // Expected routes from:
            // RootController: GET /
            // PublicController: GET /public/
            // DeepController: GET /deep/ (Wait, DeepController had prefix /deep)
            // But DeepModule is imported by PublicModule. imports don't prefix routes unless handled.
            // NestJS prefixes only if configured.
            // Here, imports just load the controllers.
            // So:
            // /
            // /public/
            // /deep/
            
            $this->assertArrayHasKey('GET', $routes);
            $getRoutes = $routes['GET'];
            
            $this->assertArrayHasKey('/', $getRoutes, 'Root route / should exist');
            $this->assertArrayHasKey('/public', $getRoutes, 'Public route /public should exist');
            $this->assertArrayHasKey('/deep', $getRoutes, 'Deep route /deep should exist');
        } else {
             $this->fail('Router is not instance of Delirium\Http\Router, cannot inspect registry.');
        }
    }
}
