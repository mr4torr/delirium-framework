<?php

declare(strict_types=1);

namespace Delirium\Core\Console\Contract;

/**
 * Interface RegenerationListenerInterface
 *
 * Defines the contract for any service that needs to regenerate a cache file
 * after a cache clear operation (Warming up).
 */
interface RegenerationListenerInterface
{
    /**
     * Check if the listener should execute.
     */
    public function shouldRun(): bool;

    /**
     * The actual logic to regenerate the cache.
     */
    public function regenerate(): void;

    /**
     * Human-readable name for feedback in the console.
     */
    public function getName(): string;
}
