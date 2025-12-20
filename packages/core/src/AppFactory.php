<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Container\Container;
use Delirium\Core\Contract\ApplicationInterface;

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

        // 2. Initialize Container
        $container = new Container();

        // 3. Initialize Router
        $router = new \Delirium\Http\Router();
        $container->set(\Delirium\Http\Contract\RouterInterface::class, $router);
        $container->set(\Delirium\Http\Router::class, $router); // Alias if needed

        // 4. Scan Module and Register Services/Controllers
        $visited = [];
        self::scanModule($moduleClass, $container, $router, $visited);
        
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
    private static function scanModule(string $moduleClass, Container $container, $router, array &$visited): void
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
                // Register as lazy singleton
                $container->set($provider, function () use ($provider) {
                    return new $provider();
                });
            } elseif (is_callable($provider)) {
                // TODO: Support callable/factory providers with ID
            }
        }

        // Register Controllers
        // Helper to get scanner. We assume specific implementation for now or we added scanClass to interface.
        // Given we are in Core, we can leverage the class directly if needed or check method.
        if ($router instanceof \Delirium\Http\Router) {
            // Instantiate scanner safely
            static $scanner = null;
            if (!$scanner) {
                // We need to access registry.
                // Assuming Router exposes registry via a getter we saw in the file content previously?
                // Yes: public function getRegistry(): RouteRegistry
                $scanner = new \Delirium\Http\Scanner\AttributeScanner($router->getRegistry());
            }
            
            foreach ($module->controllers as $controller) {
                $scanner->scanClass($controller);
            }
        }

        // Recurse Imports
        foreach ($module->imports as $import) {
            self::scanModule($import, $container, $router, $visited);
        }
    }
}
