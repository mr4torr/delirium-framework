<?php

declare(strict_types=1);

namespace Delirium\Core\Console;

use Symfony\Component\Console\Application as ConsoleApplication;

class Kernel extends ConsoleApplication
{
    private static ?self $instance = null;

    public function __construct()
    {
        parent::__construct('Delirium Framework', '1.1.0');

        self::$instance = $this;

        // Register Core Commands
        $this->addCommands([
            new Command\ServerCommand(),
            new Command\SwooleCheckCommand(),
            new Command\OptimizeCommand(),
            new Command\CacheClearCommand(),
        ]);
    }

    /**
     * Initialize core console components and wire dependencies.
     */
    public function initializeConsole(
        \Psr\Container\ContainerInterface $container,
        \Delirium\Core\Foundation\Cache\RegenerationRegistry $registry,
        \Delirium\Core\Foundation\ProviderRepository $repository,
        \Delirium\Core\Container\ContainerFactory $factory,
        string $moduleClass,
        \Delirium\Core\AppOptions $options
    ): void {
        try {
            // Wire CacheClearCommand
            if ($this->has('cache:clear')) {
                $cacheClearCommand = $this->get('cache:clear');
                if ($cacheClearCommand instanceof Command\CacheClearCommand) {
                    $cacheClearCommand->setContainer($container);
                    $cacheClearCommand->setRegistry($registry);
                }
            }

            // Wire OptimizeCommand
            if ($this->has('optimize')) {
                $optimizeCommand = $this->get('optimize');
                if ($optimizeCommand instanceof Command\OptimizeCommand) {
                    $optimizeCommand->setContainer($container);
                }
            }

            // Register Default Regeneration Listeners
            $registry->register(new Listener\DiscoveryRegenerationListener($repository));
            $registry->register(new Listener\ContainerRegenerationListener($factory, $moduleClass, $options));
        } catch (\Throwable $e) {
            // Log or ignore if commands are not found
        }
    }

    /**
     * Get the current Kernel instance (for provider access).
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }
}
