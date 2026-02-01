<?php

declare(strict_types=1);

namespace Delirium\Http;

// use Delirium\Http\Exception\DuplicateRouteException; // Will implement this or usage generic LogicException
use LogicException;

class RouteRegistry
{
    /**
     * @var array<string, array<string, mixed>>  [method => [path => handler]]
     */
    private array $routes = [];

    public function addRoute(string $method, string $path, mixed $handler): void
    {
        $upperMethod = strtoupper($method);

        if (isset($this->routes[$upperMethod][$path])) {
            throw new LogicException("Duplicate route defined: [{$upperMethod}] {$path}");
        }

        $this->routes[$upperMethod][$path] = $handler;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param array<string, array<string, mixed>> $routes
     */
    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function getHandler(string $method, string $path): mixed
    {
        return $this->routes[strtoupper($method)][$path] ?? null;
    }
}
