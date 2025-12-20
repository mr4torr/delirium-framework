<?php

declare(strict_types=1);

namespace Delirium\Core\Container;

use Psr\Container\ContainerInterface;
use Delirium\Core\Container\Exception\NotFoundException;
use Delirium\Core\Container\Exception\ContainerException;

class Container implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @var array<string, callable>
     */
    private array $definitions = [];

    /**
     * Register a service definition.
     *
     * @param string $id
     * @param callable|object $definition
     */
    public function set(string $id, callable|object $definition): void
    {
        if (is_object($definition) && !is_callable($definition)) {
            $this->instances[$id] = $definition;
        } else {
            $this->definitions[$id] = $definition;
        }
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->definitions[$id])) {
            // Auto-wiring attempt could go here, but strictly PSR-11 simply throws NotFound
             if (class_exists($id)) {
                 // Simple auto-instantiation (no constructor args support in this lightweight version yet)
                 // Or we can throw NotFound to be strict.
                 // Let's implement basic reflection instantiation for zero-arg or container-aware classes?
                 // For now, adhere strictly to definitions or instances.
                 throw new NotFoundException("Service '$id' not found in container.");
             }
            throw new NotFoundException("Service '$id' not found in container.");
        }

        try {
            $service = call_user_func($this->definitions[$id], $this);
            $this->instances[$id] = $service; // Singleton by default
            return $service;
        } catch (\Throwable $e) {
            throw new ContainerException("Error resolving service '$id': " . $e->getMessage(), 0, $e);
        }
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->definitions[$id]);
    }
}
