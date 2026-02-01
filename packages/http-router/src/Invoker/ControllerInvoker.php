<?php

declare(strict_types=1);

namespace Delirium\Http\Invoker;

use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Request\ContainerServiceResolver;
use Delirium\Http\Resolver\Request\DefaultValueResolver;
use Delirium\Http\Resolver\Request\RouteParameterResolver;
use Delirium\Http\Resolver\Request\ServerRequestResolver;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Nyholm\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use RuntimeException;

class ControllerInvoker
{
    private ?ContainerInterface $container = null;
    private ?ArgumentResolverChain $requestResolverChain = null;
    private ?ResponseResolverChain $responseResolverChain = null;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setArgumentResolverChain(ArgumentResolverChain $chain): void
    {
        $this->requestResolverChain = $chain;
    }

    public function setResponseResolverChain(ResponseResolverChain $chain): void
    {
        $this->responseResolverChain = $chain;
    }

    public function invoke(mixed $handler, array $params = [], ?ServerRequestInterface $request = null): mixed
    {
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            $instance = null;
            if (is_object($class)) {
                return $this->invokeWithReflection($class, $method, $params, $request);
            }

            /** @var string $class */
            $class = (string) $class;
            if ($this->container && $this->container->has($class)) {
                $instance = $this->container->get($class);
            }

            if (!$instance && class_exists($class)) {
                $instance = new $class();
            }

            if (!$instance) {
                throw new RuntimeException("Controller class '{$class}' not found.");
            }

            return $this->invokeWithReflection($instance, $method, $params, $request);
        }

        if (is_callable($handler)) {
            $results = $handler(...$params);
            return $this->getResponseResolverChain()->resolve($results, $request ?? new ServerRequest('GET', '/'), []);
        }

        throw new RuntimeException('Invalid handler');
    }

    private function invokeWithReflection(
        object $instance,
        string $method,
        array $params,
        ?ServerRequestInterface $request,
    ): mixed {
        $refMethod = new ReflectionMethod($instance, $method);

        // Ensure request has route params as attributes for RouteParameterResolver
        if ($request && $params !== []) {
            foreach ($params as $key => $value) {
                $request = $request->withAttribute((string) $key, $value);
            }
        }

        if (!$request) {
            throw new RuntimeException('Request object is required for argument resolution.');
        }

        // Request Resolution (Args)
        $args = $this->getRequestResolverChain()->resolveArguments($request, $refMethod->getParameters());

        // Execute Controller
        $results = $refMethod->invokeArgs($instance, $args);

        // Extract Route Attributes
        $attributes = $refMethod->getAttributes(
            \Delirium\Http\Attribute\RouteAttribute::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );
        $routeConfig = [];

        foreach ($attributes as $attribute) {
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

    private function getResponseResolverChain(): ResponseResolverChain
    {
        if ($this->responseResolverChain !== null) {
            return $this->responseResolverChain;
        }

        throw new RuntimeException('ResponseResolverChain not configured.');
    }
}
