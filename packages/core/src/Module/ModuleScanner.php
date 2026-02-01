<?php

declare(strict_types=1);

namespace Delirium\Core\Module;

use Delirium\Core\Attribute\Module;
use Delirium\DI\ContainerBuilder;
use InvalidArgumentException;
use ReflectionClass;

class ModuleScanner
{
    /**
     * @var array<string, bool>
     */
    private array $visited = [];

    public function scan(string $moduleClass, ContainerBuilder $builder): void
    {
        if (isset($this->visited[$moduleClass])) {
            return; // Cycle detected or already visited
        }
        $this->visited[$moduleClass] = true;

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
            $builder->register($controller, $controller);
        }

        // Recurse Imports
        foreach ($module->imports as $import) {
            $this->scan($import, $builder);
        }
    }
}
