<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Unit\Container;

use Delirium\Core\AppOptions;
use Delirium\Core\Attribute\Module;
use Delirium\Core\Container\ContainerFactory;
use Delirium\Core\Options\DebugOptions;
use Delirium\Http\Router;
use PHPUnit\Framework\TestCase;

#[Module]
class TestContainerModule {}

class ContainerFactoryTest extends TestCase
{
    private string $cacheFile;

    protected function setUp(): void
    {
        $this->cacheFile = getcwd() . '/var/cache/dependency-injection.php';
        if (file_exists($this->cacheFile)) unlink($this->cacheFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cacheFile)) unlink($this->cacheFile);
    }

    public function testCreateBuildsContainerWithCoreServices(): void
    {
        $factory = new ContainerFactory();
        $options = new AppOptions(new DebugOptions(debug: true));

        $container = $factory->create(TestContainerModule::class, $options);

        $this->assertTrue($container->has(Router::class));
        $this->assertTrue($container->has('router'));
        $this->assertTrue($container->has(\Psr\Http\Message\ResponseFactoryInterface::class));
    }

    public function testCreateDumpsCacheWhenNotDebug(): void
    {
        $factory = new ContainerFactory();
        $options = new AppOptions(new DebugOptions(debug: false));

        $factory->create(TestContainerModule::class, $options);

        $this->assertFileExists($this->cacheFile);
    }
}
