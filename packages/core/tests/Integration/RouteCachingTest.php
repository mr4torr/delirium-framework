<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Integration;

use Delirium\Core\AppFactory;
use Delirium\Core\AppOptions;
use Delirium\Http\Router;
use PHPUnit\Framework\TestCase;

class RouteCachingTest extends TestCase
{
    private string $cacheFile;
    private string $diCacheFile;

    protected function setUp(): void
    {
        $this->cacheFile = dirname(__DIR__, 4) . '/var/cache/http-routes.php';
        $this->diCacheFile = dirname(__DIR__, 4) . '/var/cache/dependency-injection.php';
        
        // Clean up
        if (file_exists($this->cacheFile)) unlink($this->cacheFile);
        if (file_exists($this->diCacheFile)) unlink($this->diCacheFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cacheFile)) unlink($this->cacheFile);
        if (file_exists($this->diCacheFile)) unlink($this->diCacheFile);
    }

    public function testRouteCacheIsCreatedAndLoaded(): void
    {
        // 1. First Boot: Should create cache
        // We need a dummy module. Let's use one from fixtures if available, or just mock?
        $app = AppFactory::create(
            \Delirium\Core\Tests\Fixtures\Hierarchy\RootModule::class, 
            new AppOptions(new \Delirium\Core\Options\DebugOptions(debug: false))
        );
        
        // Assert Cache Created
        $this->assertFileExists($this->cacheFile, 'Route cache file should be created on first boot (non-debug).');
        
        // 2. Second Boot: Should use cache
        // Modify the cache to prove it's being read
        $cachedContent = file_get_contents($this->cacheFile);
        // Inject a fake route
        $fakeRoutes = ['GET' => ['/injected-from-cache' => 'fake_handler']];
        file_put_contents($this->cacheFile, "<?php return " . var_export($fakeRoutes, true) . ";");
        
        // Re-boot
        $app2 = AppFactory::create(
            \Delirium\Core\Tests\Fixtures\Hierarchy\RootModule::class, 
            new AppOptions(new \Delirium\Core\Options\DebugOptions(debug: false))
        );
        
        // Verify Injected Route exists in Router
        // We need access to Router. App interface doesn't expose it directly usually?
        // But implementation does.
        $reflection = new \ReflectionClass($app2);
        $property = $reflection->getProperty('router');
        $property->setAccessible(true);
        $router = $property->getValue($app2);
        
        $routes = $router->getRegistry()->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('/injected-from-cache', $routes['GET']);
    }
}
