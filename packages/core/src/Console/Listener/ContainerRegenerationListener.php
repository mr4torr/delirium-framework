<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Listener;

use Delirium\Core\AppOptions;
use Delirium\Core\Console\Contract\RegenerationListenerInterface;
use Delirium\Core\Container\ContainerFactory;

/**
 * Class ContainerRegenerationListener
 *
 * Regenerates the Dependency Injection container cache (var/cache/dependency-injection.php).
 */
class ContainerRegenerationListener implements RegenerationListenerInterface
{
    public function __construct(
        private ContainerFactory $factory,
        private string $moduleClass,
        private AppOptions $options
    ) {}

    public function shouldRun(): bool
    {
        // Container warmup is essential for performance in production
        return true;
    }

    public function regenerate(): void
    {
        // Calling create() will trigger buildContainer() which dumps the cache if debug is false
        $this->factory->create($this->moduleClass, $this->options);
    }

    public function getName(): string
    {
        return 'DI Container';
    }
}
