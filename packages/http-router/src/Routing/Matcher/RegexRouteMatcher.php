<?php

declare(strict_types=1);

namespace Delirium\Http\Routing\Matcher;

use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

class RegexRouteMatcher implements RouteMatcherInterface
{
    /**
     * @var array<string, array<string, mixed>> [method => [route_pattern => handler]]
     */
    private array $staticRoutes = [];

    /**
     * @var array<string, array<int, array{regex: string, handler: mixed}>> [method => [[regex, handler]]]
     */
    private array $dynamicRoutes = [];

    public function add(string $method, string $path, mixed $handler): void
    {
        $method = strtoupper($method);

        // Check for parameters {name}
        if (str_contains($path, '{')) {
            $this->addDynamicRoute($method, $path, $handler);
            return;
        }

        $this->staticRoutes[$method][$path] = $handler;
    }

    private function addDynamicRoute(string $method, string $path, mixed $handler): void
    {
        // Convert {param} to (?P<param>[^/]+)
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->dynamicRoutes[$method][] = [
            'regex' => $pattern,
            'handler' => $handler,
        ];
    }

    public function match(ServerRequestInterface $request): RouteMatch
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        // 1. Static Match
        if (isset($this->staticRoutes[$method][$path])) {
            return new RouteMatch($this->staticRoutes[$method][$path], []);
        }

        // 2. Dynamic Match
        if (isset($this->dynamicRoutes[$method])) {
            foreach ($this->dynamicRoutes[$method] as $route) {
                if (!preg_match($route['regex'], $path, $matches)) {
                    continue;
                }

                $params = array_filter($matches, static fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
                return new RouteMatch($route['handler'], $params);
            }
        }

        // 3. 404 Not Found or 405 Method Not Allowed
        $this->handleNoMatch($method, $path);

        // Should be unreachable as handleNoMatch throws
        throw new RouteNotFoundException("Route not found: {$path}");
    }

    private function handleNoMatch(string $method, string $path): void
    {
        $allowedMethods = [];
        foreach ($this->staticRoutes as $m => $routes) {
            if (!($m !== $method && isset($routes[$path]))) {
                continue;
            }

            $allowedMethods[] = $m;
        }
        foreach ($this->dynamicRoutes as $m => $routes) {
            if ($m === $method) {
                continue;
            }

            foreach ($routes as $route) {
                if (!preg_match($route['regex'], $path)) {
                    continue;
                }

                $allowedMethods[] = $m;
            }
        }

        if ($allowedMethods !== []) {
            throw new MethodNotAllowedException(
                "Method {$method} not allowed. Allowed: " . implode(', ', array_unique($allowedMethods)),
            );
        }

        throw new RouteNotFoundException("Route not found: {$path}");
    }
}
