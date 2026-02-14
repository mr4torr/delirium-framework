<?php

declare(strict_types=1);

namespace Delirium\Http;

use Delirium\Core\Console\Kernel;
use Delirium\Http\Console\Command\RouteListCommand;
use Delirium\Support\ServiceProvider;

class HttpRouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No registration logic needed for now.
        // RouteRegistry and Router are registered in ContainerFactory (Core).
    }

    public function boot(): void
    {
        // Only register command if running in CLI and Kernel is available
        if (PHP_SAPI === 'cli' && class_exists(Kernel::class)) {
            $kernel = Kernel::getInstance();
            if ($kernel) {
                // Resolve RouteRegistry from container
                if ($this->container->has(RouteRegistry::class)) {
                    $registry = $this->container->get(RouteRegistry::class);
                    $command = new RouteListCommand($registry);
                    $kernel->addCommands([$command]);
                }
            }
        }
    }
}
