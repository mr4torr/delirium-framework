<?php

declare(strict_types=1);

namespace Delirium\Http\Dispatcher;

use Delirium\Http\Contract\DispatcherInterface;
use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Request\ContainerServiceResolver;
use Delirium\Http\Resolver\Request\DefaultValueResolver;
use Delirium\Http\Resolver\Request\RouteParameterResolver;
use Delirium\Http\Resolver\Request\ServerRequestResolver;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use ReflectionMethod;

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

    private ?ContainerInterface $container = null;

    public function setContainer(ContainerInterface $container): void
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
            if (is_object($class)) {
                $instance = $class;
                $class = get_class($instance);
            } else {
                /** @var string $class */
                $class = (string) $class;
                if ($this->container && $this->container->has($class)) {
                    $instance = $this->container->get($class);
                } elseif (class_exists($class)) {
                    $instance = new $class();
                } else {
                     throw new RuntimeException("Controller class '$class' not found.");
                }
            }

            return $this->invokeWithReflection($instance, $method, $params, $request);
        }

        if (is_callable($handler)) {
            $results = $handler(...$params);

            // We need to resolve this result too!
            // Attributes for closure? Closures can have attributes in PHP 8.
            $routeConfig = [];
            // Try to reflect on closure to get attributes?
            // $refFunction = new \ReflectionFunction($handler);
            // $attributes = $refFunction->getAttributes(); ...
            // keeping it simple for now, generic resolution.

            return $this->getResponseResolverChain()->resolve($results, $request ?? new ServerRequest('GET', '/'), []);
        }

        throw new RuntimeException("Invalid handler");
    }

    private ?ArgumentResolverChain $requestResolverChain = null;

    public function setArgumentResolverChain(ArgumentResolverChain $chain): void
    {
        $this->requestResolverChain = $chain;
    }

    /**
     * TODO: Fazer com que essa função seja um arquivos de configuração que permite definir as classes Resolver (Middleware)
     * @return ArgumentResolverChain|null
     */
    private function getRequestResolverChain(): ArgumentResolverChain
    {
        if ($this->requestResolverChain !== null) {
            return $this->requestResolverChain;
        }

        $resolvers = [
            new ServerRequestResolver(),
            new RouteParameterResolver(),
        ];

        if ($this->container) {
            $resolvers[] = new ContainerServiceResolver($this->container);
        }

        $resolvers[] = new DefaultValueResolver();

        return $this->requestResolverChain = new ArgumentResolverChain($resolvers);
    }


    private ?ResponseResolverChain $responseResolverChain = null;

    public function setResponseResolverChain(ResponseResolverChain $chain): void
    {
        $this->responseResolverChain = $chain;
    }

    private function getResponseResolverChain(): ResponseResolverChain
    {
        if ($this->responseResolverChain !== null) {
            return $this->responseResolverChain;
        }

        // Fallback or throws? Ideally injected.
        // We'll create minimal chain if possibly but dependencies make it hard.
        throw new RuntimeException("ResponseResolverChain not configured.");
    }

    private function invokeWithReflection(object $instance, string $method, array $params, ?ServerRequestInterface $request): mixed
    {
        $refMethod = new ReflectionMethod($instance, $method);

        // Ensure request has route params as attributes for RouteParameterResolver
        if ($request && !empty($params)) {
             foreach ($params as $key => $value) {
                 $request = $request->withAttribute((string)$key, $value);
             }
        }

        if (!$request) {
            throw new RuntimeException("Request object is required for argument resolution.");
        }

        // Request Resolution (Args)
        $args = $this->getRequestResolverChain()->resolveArguments($request, $refMethod->getParameters());

        // Execute Controller
        $results =  $refMethod->invokeArgs($instance, $args);

        // Extract Route Attributes (e.g., #[Get(type: 'json', status: 201)])
        // We look for Attributes that have 'type' or 'status' properties?
        // Or strictly Delirium Route attributes.
        // We'll assume attributes extending Delirium\Core\Attribute\Route or similar,
        // but for now we look for attributes with getArguments?
        // Actually, we can just get all attributes and merge arguments?
        // Specifically we care about the route definition.

        $attributes = $refMethod->getAttributes();
        $routeConfig = [];

        foreach ($attributes as $attribute) {
             // We could filter by Route Attribute class if we knew the base class specific to this context.
             // Assuming \Delirium\Http\Attribute\Route match.
             // For generic usage, we check if instance has 'type' or 'status'.
             $inst = $attribute->newInstance();
             if (property_exists($inst, 'type')) {
                 $routeConfig['type'] = $inst->type;
             }
             if (property_exists($inst, 'status')) {
                 $routeConfig['status'] = $inst->status;
             }
        }

        // Response Resolution (Result)
        return $this->getResponseResolverChain()->resolve($results, $request, $routeConfig);
    }
}
