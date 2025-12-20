<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Contract\ApplicationInterface;
use Delirium\DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Static Factory to bootstrap the application.
 */
class AppFactory
{
    /**
     * Create an Application instance.
     *
     * @param string $moduleClass The root module class name.
     * @param AppOptions|null $options Configuration options.
     * @return ApplicationInterface
     */
    public static function create(string $moduleClass, ?AppOptions $options = null): ApplicationInterface
    {
        // 1. Initialize Options
        $appOptions = $options ?? new AppOptions();

        // 2. Initialize Container (Cached or New)
        $cacheFile = dirname(__DIR__, 3) . '/var/cache/dependency-injection.php';
        $debug = true; // TODO: Get from options/env

        if (!$debug && file_exists($cacheFile)) {
            require_once $cacheFile;
            // The dumped container class is usually 'Delirium\DI\Cache\ProjectServiceContainer'
            /** @var ContainerInterface $container */
            $container = new \Delirium\DI\Cache\ProjectServiceContainer();
        } else {
            $builder = new ContainerBuilder();
            // Register Router as generic service first (instance)
            $router = new \Delirium\Http\Router();
            $builder->getInnerBuilder()->set(\Delirium\Http\Contract\RouterInterface::class, $router);
            $builder->getInnerBuilder()->set(\Delirium\Http\Router::class, $router);

            // Scan Module
            $visited = [];
            self::scanModule($moduleClass, $builder, $router, $visited);

            // Build and Dump
            $builder->build('dev'); // env
            $builder->dump($cacheFile);
            
            // For the immediate usage, we return the builder's compiled container
            // However, after compile, we can't add more?
            // Yes, compile happens in build.
            $container = $builder->getInnerBuilder();
        }

        // 3. Ensure Router is available if we loaded from cache
        if (!$container->has(\Delirium\Http\Router::class)) {
            // If cached container, router should be there if registered?
            // Instance services (synthetic) are tricky with dumping.
            // Usually we need to set them at runtime on the cached container.
            $router = new \Delirium\Http\Router();
            if (method_exists($container, 'set')) {
               $container->set(\Delirium\Http\Contract\RouterInterface::class, $router);
               $container->set(\Delirium\Http\Router::class, $router);
            }
        } else {
             $router = $container->get(\Delirium\Http\Router::class);
        }

        if (method_exists($router, 'setContainer')) {
            $router->setContainer($container);
        }

        // 5. Create Application
        return new Application($container, $appOptions, $router);
    }

    /**
     * Recursively scan modules.
     *
     * @param string $moduleClass
     * @param Container $container
     * @param \Delirium\Http\Contract\RouterInterface $router
     * @param array<string, bool> $visited
     */
    private static function scanModule(string $moduleClass, ContainerBuilder $builder, $router, array &$visited): void
    {
        if (isset($visited[$moduleClass])) {
            return; // Cycle detected or already visited
        }
        $visited[$moduleClass] = true;

        if (!class_exists($moduleClass)) {
            throw new \InvalidArgumentException("Module class '$moduleClass' not found.");
        }

        $ref = new \ReflectionClass($moduleClass);
        $attributes = $ref->getAttributes(\Delirium\Core\Attribute\Module::class);

        if (empty($attributes)) {
            throw new \InvalidArgumentException("Class '$moduleClass' is not annotated with #[AppModule].");
        }

        /** @var \Delirium\Core\Attribute\Module $module */
        $module = $attributes[0]->newInstance();

        // Register Providers
        foreach ($module->providers as $provider) {
            if (is_string($provider)) {
                $builder->register($provider, $provider);
            } elseif (is_callable($provider)) {
                // TODO: Support callable/factory providers with ID
            }
        }

        // Register Controllers
        if ($router instanceof \Delirium\Http\Router) {
            static $scanner = null;
            if (!$scanner) {
                $scanner = new \Delirium\Http\Scanner\AttributeScanner($router->getRegistry());
            }
            
            foreach ($module->controllers as $controller) {
                // Registration in DI
                $builder->register($controller, $controller);
                
                // Registration in Router (Routes)
                $scanner->scanClass($controller);
            }
        }

        // Recurse Imports
        foreach ($module->imports as $import) {
            self::scanModule($import, $builder, $router, $visited);
        }
    }
}
