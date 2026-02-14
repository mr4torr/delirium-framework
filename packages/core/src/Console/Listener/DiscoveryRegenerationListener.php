<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Listener;

use Delirium\Core\Console\Contract\RegenerationListenerInterface;
use Delirium\Core\Foundation\ProviderRepository;

/**
 * Class DiscoveryRegenerationListener
 *
 * Regenerates the provider discovery cache (var/cache/discovery.php).
 */
class DiscoveryRegenerationListener implements RegenerationListenerInterface
{
    public function __construct(
        private ProviderRepository $repository
    ) {}

    public function shouldRun(): bool
    {
        // Core discovery should always be warmed up
        return true;
    }

    public function regenerate(): void
    {
        $this->repository->cache();
    }

    public function getName(): string
    {
        return 'Provider Discovery';
    }
}
