<?php

declare(strict_types=1);

namespace Delirium\DI\Compiler;

use Delirium\DI\Attribute\Inject;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

class DiscoveryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // 1. Collect initial set of classes to scan (existing definitions)
        $queue = [];
        $scanned = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if ($class && class_exists($class)) {
                $queue[] = $class;
            }
        }

        // 2. BFS to discover dependencies
        while ($queue !== []) {
            $class = array_shift($queue);

            if (isset($scanned[$class])) {
                continue;
            }
            $scanned[$class] = true;

            try {
                $ref = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                continue;
            }

            $dependencies = [];

            // A. Constructor Dependencies
            $dependencies = [];

            // A. Constructor Dependencies
            $constructor = $ref->getConstructor();
            if ($constructor) {
                foreach ($constructor->getParameters() as $param) {
                    $type = $param->getType();
                    if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                        $dependencies[] = $type->getName();
                    }
                }
            }

            // B. Property Dependencies (#[Inject])
            foreach ($ref->getProperties() as $property) {
                $attributes = $property->getAttributes(Inject::class);
                if ($attributes !== []) {
                    // Check explicit serviceId
                    $attr = $attributes[0]->newInstance();
                    if ($attr->serviceId) {
                        $dependencies[] = $attr->serviceId;
                        continue;
                    }

                    // Infer from type
                    $type = $property->getType();
                    if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                        $dependencies[] = $type->getName();
                    }
                }
            }

            // C. Route Method Dependencies (Controllers)
            if (str_ends_with($class, 'Controller')) {
                foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    foreach ($method->getParameters() as $param) {
                        $type = $param->getType();
                        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                            $dependencies[] = $type->getName();
                        }
                    }
                }
            }

            // Process dependencies
            foreach ($dependencies as $depClass) {
                // Determine if valid class
                if (!(class_exists($depClass) || interface_exists($depClass))) {
                    continue;
                }

                if ($depClass === PsrContainerInterface::class || $depClass === SymfonyContainerInterface::class) {
                    continue;
                }

                if (!$container->has($depClass) && !$container->hasDefinition($depClass)) {
                    // Register it!
                    // echo "Implicitly registering: $depClass (found in $class)\n";
                    $container
                        ->register($depClass, $depClass)
                        ->setAutowired(true)
                        ->setAutoconfigured(true)
                        ->setPublic(true);

                    // Add to queue to scan ITS dependencies
                    $queue[] = $depClass;
                }
            }
        }
    }
}
