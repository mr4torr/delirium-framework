<?php

declare(strict_types=1);

namespace Delirium\Http;

use Delirium\Http\Contract\RouterInterface;
use Delirium\Http\Contract\DispatcherInterface;
use Delirium\Http\Scanner\AttributeScanner;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    private RouteRegistry $registry;
    private AttributeScanner $scanner;
    private ?DispatcherInterface $dispatcher = null;

    public function __construct()
    {
        $this->registry = new RouteRegistry();
        $this->scanner = new AttributeScanner($this->registry);
    }

    public function scan(string $directory): void
    {
        $this->scanner->scanDirectory($directory);
    }

    public function register(string|array $methods, string $path, callable|array $handler): void
    {
        $methods = (array) $methods;
        foreach ($methods as $method) {
            $this->registry->addRoute($method, $path, $handler);
        }
    }

    private mixed $container = null; // ContainerInterface|null

    public function setContainer(mixed $container): void
    {
        $this->container = $container;
        if ($this->dispatcher && method_exists($this->dispatcher, 'setContainer')) {
            $this->dispatcher->setContainer($container);
        }
    }

    private bool $compiled = false;

    public function dispatch(ServerRequestInterface $request): mixed
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new Dispatcher\RegexDispatcher();
            if ($this->container && method_exists($this->dispatcher, 'setContainer')) {
                $this->dispatcher->setContainer($this->container);
            }
        }

        if (!$this->compiled) {
            $this->compileRoutes();
        }
        
        return $this->dispatcher->dispatch($request);
    }
    
    private function compileRoutes(): void
    {
        $allRoutes = $this->registry->getRoutes();
        foreach ($allRoutes as $method => $routes) {
            foreach ($routes as $path => $handler) {
                // We add to dispatcher. Note: Dispatcher might duplicate if already added via addRoute.
                // But typically scan() populates registry, and direct calls populate registry (via register method).
                // Wait, register() adds to registry. So just syncing registry is enough.
                $this->dispatcher->addRoute($method, $path, $handler);
            }
        }
        $this->compiled = true;
    }
    
    public function setDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
        // Also we might need to populate the dispatcher from registry here
        // But implementation detail of dispatcher might differ (e.g. inject registry into dispatcher).
    }
    
    public function getRegistry(): RouteRegistry
    {
        return $this->registry;
    }
}
