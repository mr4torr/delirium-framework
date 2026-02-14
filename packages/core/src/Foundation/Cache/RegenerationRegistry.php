<?php

declare(strict_types=1);

namespace Delirium\Core\Foundation\Cache;

use Delirium\Core\Console\Contract\RegenerationListenerInterface;

/**
 * Class RegenerationRegistry
 *
 * Collects and manages RegenerationListenerInterface implementations.
 */
class RegenerationRegistry
{
    /**
     * @var RegenerationListenerInterface[]
     */
    private array $listeners = [];

    /**
     * Register a new regeneration listener.
     */
    public function register(RegenerationListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * Get all registered listeners.
     *
     * @return RegenerationListenerInterface[]
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }
}
