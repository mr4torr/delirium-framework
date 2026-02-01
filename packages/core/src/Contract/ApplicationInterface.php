<?php

declare(strict_types=1);

namespace Delirium\Core\Contract;

interface ApplicationInterface
{
    /**
     * Start the application server.
     *
     * @param int $port Port to listen on (default 9501).
     * @param string $host Host to bind to (default 0.0.0.0).
     * @return void
     */
    public function listen(int $port = 9501, string $host = '0.0.0.0'): void;

    /**
     * Get the DI Container instance.
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer();

    /**
     * Register a service provider for a specific environment.
     *
     * @param class-string<\Delirium\Support\ServiceProvider> $provider
     * @param string $env Environment tag: 'all', 'dev', or 'prod'
     *
     * @throws \InvalidArgumentException If provider class does not exist
     */
    public function register(string $provider, string $env = 'all'): self;

    /**
     * Register a class alias.
     *
     * @param string $alias Short name (e.g., 'Route')
     * @param class-string $class Full class name (e.g., 'Delirium\Http\Router')
     *
     * @throws \InvalidArgumentException If target class does not exist
     */
    public function alias(string $alias, string $class): self;

    /**
     * Shutdown the application server.
     *
     * @return void
     */
    public function shutdown(): void;
}
