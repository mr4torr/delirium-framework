<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

interface DispatcherInterface
{
    /**
     * Matches the request URI and Method against routes.
     * Returns the handler results (with parameters possibly injected) or throws exception.
     */
    public function dispatch(ServerRequestInterface $request): mixed;

    /**
     * Adds a route to the dispatcher's table.
     */
    public function addRoute(string $method, string $path, mixed $handler): void;

    public function setContainer(ContainerInterface $container): void;
}
