<?php

namespace Delirium\DI\Tests\Unit;

use Delirium\DI\ContainerBuilder;
use Delirium\DI\Tests\Fixtures\ConstructorInjectedService;
use Delirium\DI\Tests\Fixtures\ImplicitController;
use Delirium\DI\Tests\Fixtures\PropertyInjectedService;
use Delirium\DI\Tests\Fixtures\SimpleService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerBuilderTest extends TestCase
{
    public function testBuildReturnsCompiledContainer(): void
    {
        $builder = new ContainerBuilder();
        $container = $builder->build('test');
        
        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertTrue($container->isCompiled());
    }

    public function testManualRegistration(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(SimpleService::class, SimpleService::class);
        $container = $builder->build('test');
        
        $this->assertTrue($container->has(SimpleService::class));
        $this->assertInstanceOf(SimpleService::class, $container->get(SimpleService::class));
    }

    public function testConstructorInjectionWithImplicitDiscovery(): void
    {
        $builder = new ContainerBuilder();
        // Register the dependent service only. DiscoveryPass should find SimpleService.
        $builder->register(ConstructorInjectedService::class, ConstructorInjectedService::class);
        
        $container = $builder->build('test');
        
        // Assert dependent is present
        $this->assertTrue($container->has(ConstructorInjectedService::class));
        $service = $container->get(ConstructorInjectedService::class);
        $this->assertInstanceOf(ConstructorInjectedService::class, $service);
        
        // Assert dependency was automatically found and injected
        $this->assertTrue($container->has(SimpleService::class));
        $this->assertInstanceOf(SimpleService::class, $service->service);
    }

    public function testPropertyInjectionWithImplicitDiscovery(): void
    {
        $builder = new ContainerBuilder();
        // Register service with #[Inject]
        $builder->register(PropertyInjectedService::class, PropertyInjectedService::class);
        
        $container = $builder->build('test');
        
        $this->assertTrue($container->has(PropertyInjectedService::class));
        $service = $container->get(PropertyInjectedService::class);
        
        // Verify property injection
        $this->assertTrue(isset($service->service));
        $this->assertInstanceOf(SimpleService::class, $service->service);
        $this->assertEquals('Hello Property', $service->greet());
        
        // Verify SimpleService was implicitly registered due to #[Inject] scanning
        $this->assertTrue($container->has(SimpleService::class));
    }

    public function testImplicitControllerDiscovery(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(ImplicitController::class, ImplicitController::class);
        
        $container = $builder->build('test');
        
        // ImplicitController should be registered
        $this->assertTrue($container->has(ImplicitController::class));
        
        // SimpleService should be discovered via 'action(SimpleService $service)'
        // assuming DiscoveryPass scans Controller methods.
        $this->assertTrue($container->has(SimpleService::class));
    }
}
