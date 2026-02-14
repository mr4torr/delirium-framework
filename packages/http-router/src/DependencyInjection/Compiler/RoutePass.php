<?php

declare(strict_types=1);

namespace Delirium\Http\DependencyInjection\Compiler;

use Delirium\Http\RouteRegistry;
use Delirium\Http\Scanner\AttributeScanner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoutePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // 1. Ensure RouteRegistry is available
        // If the service is not defined, we cannot add routes to it.
        // It might be registered by the module/extension.
        if (!$container->hasDefinition(RouteRegistry::class)) {
            return;
        }

        $registryDefinition = $container->getDefinition(RouteRegistry::class);

        // 2. We use a temporary runtime registry/scanner to extract routes from classes
        $tempRegistry = new RouteRegistry();
        $scanner = new AttributeScanner($tempRegistry);

        // 3. Iterate over all definitions to find Controllers
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();

            // Skip if no class or if class doesn't exist (e.g. factory service without class set initially)
            if (!$class || !class_exists($class)) {
                continue;
            }

            // Process all prefix tags
            $prefixes = [''];
            if ($definition->hasTag('delirium.http.prefix')) {
                $tags = $definition->getTag('delirium.http.prefix');
                $prefixes = array_column($tags, 'path');
            }

            // Just delegate to scanner for each prefix.
            foreach ($prefixes as $prefix) {
                $scanner->scanClass($class, $prefix);
            }
        }

        // 4. Transfer collected routes to the service definition
        foreach ($tempRegistry->getRoutes() as $method => $routes) {
            foreach ($routes as $path => $handler) {
                // $handler in Scanner is [$className, $methodName]

                // We add a method call to `addRoute` on the RouteRegistry service.
                // start with addRoute(string $method, string $path, mixed $handler)

                $registryDefinition->addMethodCall('addRoute', [
                    $method,
                    $path,
                    $handler,
                ]);
            }
        }
    }
}
