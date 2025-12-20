<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Scans the given directory/class map for route attributes and registers them.
     */
    public function scan(string $directory): void;

    /**
     * Manually registers a route.
     *
     * @param string|array $methods
     * @param string $path
     * @param callable|array $handler
     */
    public function register(string|array $methods, string $path, callable|array $handler): void;

    /**
     * Dispatches the incoming request to the matching handler.
     */
    public function dispatch(ServerRequestInterface $request): mixed;
}
