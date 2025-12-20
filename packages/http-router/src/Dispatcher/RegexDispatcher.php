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

    public function dispatch(ServerRequestInterface $request): mixed
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 1. Static Match
        if (isset($this->staticRoutes[$method][$path])) {
            return $this->executeHandler($this->staticRoutes[$method][$path]);
        }
        
        // 2. Dynamic Match
        if (isset($this->dynamicRoutes[$method])) {
            foreach ($this->dynamicRoutes[$method] as $route) {
                if (preg_match($route['regex'], $path, $matches)) {
                    // Filter integer keys
                    $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
                    
                    // Inject params into request attributes? Or pass to handler?
                    // "Acceptance Scenario: ... controller receives 123 as an argument"
                    // To do this, we need to know the handler capability.
                    // If handler is callable, we invoke it with params.
                    // Ideally we also put them in Request Attributes.
                    
                    foreach ($params as $key => $value) {
                        $request = $request->withAttribute($key, $value);
                    }
                    
                    return $this->executeHandler($route['handler'], $params, $request);
                }
            }
        }
        
        // 3. 404 Not Found or 405 Method Not Allowed
        // Check if path exists in other methods for 405
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
        // $handler should be [Class, Method] or Callable
        
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            // Instantiation strategy? For MVP, just new Class.
            // In real framework, Container gets it.
            // We don't have container injected here yet.
            // Assumption: Simple instantiation for MVP.
            
            $instance = new $class();
            
            // Argument interaction using Reflection to map params
            // If param is found in $params, pass it.
            // If type hinted ServerRequestInterface, pass $request.
            
            // Note: This logic belongs to a Resolver, but put here for basic US3 fulfillment.
            
            // Simple approach: Pass Request if first arg, then Params?
            // Acceptance SC-001: "1 controller and 1 attribute".
            // Acceptance 3 SC-003: "type compatibility with ServerRequestInterface".
            // Acceptance 3 Scenario 1: "controller receives 123 as an argument".
            
            // We need reflection to map arguments.
            return $this->invokeWithReflection($instance, $method, $params, $request);
        }
        
        if (is_callable($handler)) {
            return $handler(...$params); // Simplistic
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
            
            if ($type && !$type->isBuiltin() && $type->getName() === ServerRequestInterface::class) {
                $args[] = $request;
            } elseif (isset($params[$name])) {
                $args[] = $params[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Missing parameter '$name' for route handler.");
            }
        }
        
        return $refMethod->invokeArgs($instance, $args);
    }
}
