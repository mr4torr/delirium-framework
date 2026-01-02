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
     * Shutdown the application server.
     *
     * @return void
     */
    public function shutdown(): void;
}
