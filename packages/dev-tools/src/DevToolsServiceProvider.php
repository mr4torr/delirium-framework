<?php

declare(strict_types=1);

namespace Delirium\DevTools;

use Delirium\Core\Console\Kernel;
use Delirium\DevTools\Console\Commands\ServerWatchCommand;
use Delirium\Support\ServiceProvider;

/**
 * Service Provider for DevTools package.
 *
 * Registers development-only tools like the ServerWatchCommand.
 */
final class DevToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register commands with Console Kernel if in console context
        $kernel = Kernel::getInstance();
        if ($kernel !== null) {
            $kernel->addCommands([new ServerWatchCommand()]);
        }
        // No services to register in DI container for now
    }

    public function boot(): void
    {
    }
}
