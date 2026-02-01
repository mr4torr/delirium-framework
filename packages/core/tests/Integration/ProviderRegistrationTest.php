<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Integration;

use Delirium\Core\Application;
use Delirium\Core\Foundation\ProviderRepository;
use Delirium\Support\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ProviderRegistrationTest extends TestCase
{
    private string $cacheFile;

    protected function setUp(): void
    {
        $this->cacheFile = sys_get_temp_dir() . '/test_integration_' . uniqid() . '.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function test_provider_registration_flow_end_to_end(): void
    {
        // Create a mock container
        $container = $this->createMock(ContainerInterface::class);

        // Create repository
        $repository = new ProviderRepository($container, $this->cacheFile, 'prod');

        // Register a provider
        $repository->register(IntegrationTestProvider::class, 'all');

        // Load and boot
        $repository->load();
        $repository->boot();

        // Verify lifecycle
        $this->assertTrue(IntegrationTestProvider::$registerCalled, 'register() should be called');
        $this->assertTrue(IntegrationTestProvider::$bootCalled, 'boot() should be called after register()');
    }

    public function test_environment_specific_provider_loading(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        // Test with 'dev' environment
        $devRepository = new ProviderRepository($container, $this->cacheFile . '_dev', 'dev');
        $devRepository->register(DevOnlyProvider::class, 'dev');
        $devRepository->register(AllEnvProvider::class, 'all');
        $devRepository->load();

        $this->assertTrue(DevOnlyProvider::$registerCalled, 'Dev provider should be loaded in dev environment');
        $this->assertTrue(AllEnvProvider::$registerCalled, 'All-env provider should always be loaded');

        // Reset
        DevOnlyProvider::$registerCalled = false;
        AllEnvProvider::$registerCalled = false;

        // Test with 'prod' environment
        $prodRepository = new ProviderRepository($container, $this->cacheFile . '_prod', 'prod');
        $prodRepository->register(DevOnlyProvider::class, 'dev');
        $prodRepository->register(AllEnvProvider::class, 'all');
        $prodRepository->load();

        $this->assertFalse(DevOnlyProvider::$registerCalled, 'Dev provider should NOT be loaded in prod environment');
        $this->assertTrue(AllEnvProvider::$registerCalled, 'All-env provider should always be loaded');
    }

    public function test_caching_and_loading_from_cache(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        // First boot: register and cache
        $repository1 = new ProviderRepository($container, $this->cacheFile, 'prod');
        $repository1->register(IntegrationTestProvider::class, 'all');
        $repository1->cache();

        $this->assertFileExists($this->cacheFile, 'Cache file should be created');

        // Reset state
        IntegrationTestProvider::$registerCalled = false;

        // Second boot: load from cache (no manual registration)
        $repository2 = new ProviderRepository($container, $this->cacheFile, 'prod');
        $repository2->load();

        $this->assertTrue(IntegrationTestProvider::$registerCalled, 'Provider should be loaded from cache');
    }
}

// Test Provider fixtures
class IntegrationTestProvider extends ServiceProvider
{
    public static bool $registerCalled = false;
    public static bool $bootCalled = false;

    public function register(): void
    {
        self::$registerCalled = true;
    }

    public function boot(): void
    {
        self::$bootCalled = true;
    }
}

class DevOnlyProvider extends ServiceProvider
{
    public static bool $registerCalled = false;

    public function register(): void
    {
        self::$registerCalled = true;
    }
}

class AllEnvProvider extends ServiceProvider
{
    public static bool $registerCalled = false;

    public function register(): void
    {
        self::$registerCalled = true;
    }
}
