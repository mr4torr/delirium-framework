<?php

declare(strict_types=1);

namespace Delirium\Support;

use Psr\Container\ContainerInterface;

/**
 * Base class for Service Providers following the two-phase boot pattern.
 *
 * Service Providers are responsible for registering services into the DI container
 * and performing initialization logic after all providers are registered.
 *
 * Lifecycle:
 * 1. register() - Called for all providers, binds services to container
 * 2. boot() - Called after all registrations, performs initialization
 *
 * @see https://refactoring.guru/design-patterns/abstract-factory Pattern: Abstract Factory
 * @see https://refactoring.guru/design-patterns/template-method Pattern: Template Method
 */
abstract class ServiceProvider
{
    /**
     * @param ContainerInterface $container The DI container instance
     */
    public function __construct(
        protected readonly ContainerInterface $container
    ) {
    }

    /**
     * Register services into the DI container.
     *
     * This method is called during the registration phase. You should ONLY bind
     * services to the container here. Do NOT resolve any services or perform
     * initialization logic.
     *
     * Example:
     * ```php
     * public function register(): void
     * {
     *     $this->container->register(MyService::class);
     *     $this->container->alias('my.service', MyService::class);
     * }
     * ```
     *
     * @return void
     */
    abstract public function register(): void;

    /**
     * Bootstrap services after all providers are registered.
     *
     * This method is called after ALL providers have completed their register()
     * phase. It is safe to resolve services from the container and perform
     * initialization logic here.
     *
     * Example:
     * ```php
     * public function boot(): void
     * {
     *     $service = $this->container->get(MyService::class);
     *     $service->initialize();
     * }
     * ```
     *
     * @return void
     */
    public function boot(): void
    {
        // Default implementation: no-op
        // Providers can override this if they need boot logic
    }
}
