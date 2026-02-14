<?php

declare(strict_types=1);

namespace Delirium\Core\Module;

use Delirium\Core\Attribute\Module;
use Delirium\Core\Attribute\ModuleImport;
use Delirium\DI\ContainerBuilder;
use InvalidArgumentException;
use ReflectionClass;

class ModuleScanner
{
    /**
     * @var array<string, bool>
     */
    private array $visited = [];

    public function scan(string $moduleClass, ContainerBuilder $builder, string $prefix = ''): void
    {
        $visitKey = $moduleClass . ':' . $prefix;
        if (isset($this->visited[$visitKey])) {
            return; // Already visited this module with this prefix
        }
        $this->visited[$visitKey] = true;

        if (!class_exists($moduleClass)) {
            throw new InvalidArgumentException("Module class '{$moduleClass}' not found.");
        }

        $ref = new ReflectionClass($moduleClass);
        $attributes = $ref->getAttributes(Module::class);

        if ($attributes === []) {
            throw new InvalidArgumentException("Class '{$moduleClass}' is not annotated with #[Module].");
        }

        /** @var Module $module */
        $module = $attributes[0]->newInstance();

        // Register Providers
        foreach ($module->providers as $provider) {
            if (is_string($provider)) {
                $builder->register($provider, $provider);
                continue;
            }
            if (is_callable($provider)) {
                // TODO(@mr4torr): Support callable/factory providers with ID
            }
        }

        // Register Controllers
        foreach ($module->controllers as $controller) {
            // Registration in DI
            if ($builder->hasDefinition($controller)) {
                $definition = $builder->getDefinition($controller);
            } else {
                $definition = $builder->register($controller, $controller);
            }

            $definition->addTag('delirium.http.prefix', ['path' => $prefix]);
        }

        // Recurse Imports
        foreach ($module->imports as $import) {
            if ($import instanceof ModuleImport) {
                $this->scan($import->class, $builder, $prefix . '/' . ltrim($import->path, '/'));
            } elseif (is_string($import)) {
                $this->scan($import, $builder, $prefix);
            }
        }
    }
}
