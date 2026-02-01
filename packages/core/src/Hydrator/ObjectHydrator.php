<?php

declare(strict_types=1);

namespace Delirium\Core\Hydrator;

use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Throwable;

class ObjectHydrator
{
    /**
     * Hydrates an object from an array using reflection (Best Effort).
     *
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $data
     * @return T
     */
    public function hydrate(string $className, array $data): object
    {
        $reflection = new ReflectionClass($className);

        // 1. Identify constructor parameters
        $constructorArgs = [];
        $constructor = $reflection->getConstructor();

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();

                if (array_key_exists($name, $data)) {
                    $constructorArgs[$name] = $data[$name];

                    // TODO(@mr4torr): Basic type coercion or check?
                    // For now, strict types in invocation will catch errors, or we rely on loose types.
                    continue;
                }

                if ($param->isDefaultValueAvailable()) {
                    $constructorArgs[$name] = $param->getDefaultValue();
                }

                // Else: if required and missing, PHP will throw generic ArgumentCountError on invoke.
                // We could catch it and throw a clearer HydrationException.
            }
        }

        // 2. Instantiate
        try {
            // Unpack associative array into positional args for valid constructor call
            // ReflectionClass::newInstanceArgs needs positional array,
            // but we have named parameters (PHP 8 supports named args, but newInstanceArgs expects list?)
            // Actually newInstanceArgs expects indexed array.
            // But we can use new instance with named arguments syntax invoke:
            // return new $className(...$constructorArgs);

            $instance = new $className(...$constructorArgs);
        } catch (Throwable $e) {
            // Fallback for simple creation if constructor fails? No, if it fails, it fails.
            throw new RuntimeException("Failed to instantiate {$className}: " . $e->getMessage(), 0, $e);
        }

        // 3. Fill remaining public properties not in constructor (or if constructor didn't cover them and they are settable)
        // Note: Constructor Promotion properties are already existing properties.

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();

            // If property was initialized via constructor, we shouldn't overwrite unless data has it
            // (Constructor args took priority above).
            // But if it wasn't in constructor args, or constructor didn't set it (less likely for DTOs),
            // OR if it's a property NOT in constructor at all (e.g. public $prop;).

            // If the property is promoted, it was already handled by constructor IF passing args worked.
            // Check if property is initialized?
            if ($property->isInitialized($instance)) {
                // If data has it, do we overwrite? Usually yes for setters, but for immutable DTOs, they are readonly.
                // If readonly, we can't write again.
                if ($property->isReadOnly()) {
                    continue;
                }
            }

            if (array_key_exists($name, $data)) {
                if (!$property->isReadOnly()) {
                    $property->setValue($instance, $data[$name]);
                }
            }
        }

        return $instance;
    }
}
