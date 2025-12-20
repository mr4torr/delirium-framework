<?php

declare(strict_types=1);

namespace Delirium\Http\Dispatcher;

use Delirium\Http\Contract\DispatcherInterface;
use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

class RegexDispatcher implements DispatcherInterface
{
    /**
     * @var array<string, array<string, mixed>> [method => [route_pattern => handler]]
     */
    private array $staticRoutes = [];
    
    /**
     * @var array<string, array<string, mixed, array>> [method => [[regex, handler, paramNames]]]
     */
    private array $dynamicRoutes = [];

    public function addRoute(string $method, string $path, mixed $handler): void
    {
        $method = strtoupper($method);
        
        // Check for parameters {name}
        if (str_contains($path, '{')) {
            $this->addDynamicRoute($method, $path, $handler);
        } else {
            $this->staticRoutes[$method][$path] = $handler;
        }
    }

    private function addDynamicRoute(string $method, string $path, mixed $handler): void
    {
        // Convert {param} to (?P<param>[^/]+)
        // Also escape other characters
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        // Extract param names explicitly if needed, but (?P<name>) handles it in matches.
        
        $this->dynamicRoutes[$method][] = [
            'regex' => $pattern,
            'handler' => $handler
        ];
    }

    private ?\Psr\Container\ContainerInterface $container = null;

    public function setContainer(\Psr\Container\ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function dispatch(ServerRequestInterface $request): mixed
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 1. Static Match
        if (isset($this->staticRoutes[$method][$path])) {
            return $this->executeHandler($this->staticRoutes[$method][$path], [], $request);
        }
        
        // 2. Dynamic Match
        if (isset($this->dynamicRoutes[$method])) {
            foreach ($this->dynamicRoutes[$method] as $route) {
                if (preg_match($route['regex'], $path, $matches)) {
                    // Filter integer keys
                    $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
                    
                    foreach ($params as $key => $value) {
                        $request = $request->withAttribute((string)$key, $value);
                    }
                    
                    return $this->executeHandler($route['handler'], $params, $request);
                }
            }
        }
        
        // 3. 404 Not Found or 405 Method Not Allowed
        $allowedMethods = [];
        foreach ($this->staticRoutes as $m => $routes) {
            if ($m !== $method && isset($routes[$path])) {
                $allowedMethods[] = $m;
            }
        }
        foreach ($this->dynamicRoutes as $m => $routes) {
            if ($m !== $method) {
                foreach ($routes as $route) {
                    if (preg_match($route['regex'], $path)) {
                        $allowedMethods[] = $m;
                    }
                }
            }
        }
        
        if (!empty($allowedMethods)) {
            throw new MethodNotAllowedException("Method $method not allowed. Allowed: " . implode(', ', array_unique($allowedMethods)));
        }

        throw new RouteNotFoundException("Route not found: $path");
    }

    private function executeHandler(mixed $handler, array $params = [], ?ServerRequestInterface $request = null): mixed
    {
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            
            $instance = null;
            if ($this->container && $this->container->has($class)) {
                $instance = $this->container->get($class);
            } elseif (class_exists($class)) {
                // Fallback if not in container but exists (e.g. simple controller)
                // Though with DI we prefer container use.
                // However, constructor injection relies on container.
                // If class exists but not in container, simple new() might fail dependencies.
                // US4 Implicit Registration implies it should be in container or auto-wired.
                // If it's not in container, we try new() but if it has deps it fails.
                $instance = new $class();
            } else {
                 throw new \RuntimeException("Controller class '$class' not found.");
            }
            
            return $this->invokeWithReflection($instance, $method, $params, $request);
        }
        
        if (is_callable($handler)) {
            return $handler(...$params); 
        }
        
        throw new \RuntimeException("Invalid handler");
    }
    
    private function invokeWithReflection(object $instance, string $method, array $params, ?ServerRequestInterface $request): mixed
    {
        $refMethod = new \ReflectionMethod($instance, $method);
        $args = [];
        
        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();
            $typeName = $type && !$type->isBuiltin() ? $type->getName() : null;
            
            // Priority 1: ServerRequestInterface
            if ($typeName === ServerRequestInterface::class && $request) {
                $args[] = $request;
                continue;
            }
            // Priority 2: Route Parameters
            if (isset($params[$name])) {
                $args[] = $params[$name];
                continue;
            }
            // Priority 3: Container Service (Method Injection)
            if ($typeName && $this->container && $this->container->has($typeName)) {
                $args[] = $this->container->get($typeName);
                continue;
            }
            // Priority 4: Default Value
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }
            
            throw new \RuntimeException("Missing parameter '$name' for route handler '{$refMethod->class}::$method'.");
        }
        
        return $refMethod->invokeArgs($instance, $args);
    }
}
