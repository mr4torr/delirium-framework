<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Foundation;

use Delirium\Core\Foundation\ProviderRepository;
use Delirium\Support\ServiceProvider;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ProviderRepositoryTest extends TestCase
{
    private ContainerInterface $container;
    private string $cacheFile;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->cacheFile = sys_get_temp_dir() . '/test_discovery_' . uniqid() . '.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function test_register_adds_provider_to_manifest(): void
    {
        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->register(TestProvider::class, 'all');

        $providers = $repository->getProviders();

        $this->assertContains(TestProvider::class, $providers['all']);
    }

    public function test_register_throws_for_non_existent_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider class [NonExistentClass] does not exist');

        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->register('NonExistentClass', 'all');
    }

    public function test_register_supports_environment_filtering(): void
    {
        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->register(TestProvider::class, 'dev');
        $repository->register(AnotherTestProvider::class, 'all');

        $providers = $repository->getProviders();

        $this->assertContains(TestProvider::class, $providers['dev']);
        $this->assertContains(AnotherTestProvider::class, $providers['all']);
    }

    public function test_load_instantiates_and_registers_providers(): void
    {
        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->register(TestProvider::class, 'all');
        $repository->load();

        // Provider's register() should have been called
        $this->assertTrue(TestProvider::$registerCalled);
    }

    public function test_boot_calls_boot_on_loaded_providers(): void
    {
        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->register(TestProvider::class, 'all');
        $repository->load();
        $repository->boot();

        // Provider's boot() should have been called
        $this->assertTrue(TestProvider::$bootCalled);
    }

    public function test_cache_writes_manifest_to_file(): void
    {
        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->register(TestProvider::class, 'all');
        $repository->cache();

        $this->assertFileExists($this->cacheFile);

        $manifest = require $this->cacheFile;
        $this->assertIsArray($manifest);
        $this->assertContains(TestProvider::class, $manifest['all']);
    }

    public function test_load_uses_cache_if_available(): void
    {
        // Create a cache file manually
        $manifest = [
            'all' => [TestProvider::class],
            'dev' => [],
            'prod' => [],
        ];

        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= 'declare(strict_types=1);' . PHP_EOL . PHP_EOL;
        $content .= 'return ' . var_export($manifest, true) . ';' . PHP_EOL;
        file_put_contents($this->cacheFile, $content);

        // Create repository without registering anything
        $repository = new ProviderRepository($this->container, $this->cacheFile, 'prod');
        $repository->load();

        // Provider from cache should have been loaded
        $this->assertTrue(TestProvider::$registerCalled);
    }
}

// Test Provider fixtures
class TestProvider extends ServiceProvider
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

class AnotherTestProvider extends ServiceProvider
{
    public function register(): void
    {
        // No-op for testing
    }
}
