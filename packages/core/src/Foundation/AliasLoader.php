<?php

declare(strict_types=1);

namespace Delirium\Core\Foundation;

use InvalidArgumentException;

/**
 * Manager for class aliases (Facade-like shortcuts).
 *
 * This class provides a centralized way to register and activate class aliases
 * using PHP's native class_alias() function. Aliases are global to the PHP process,
 * making them safe for Swoole's long-running environment.
 *
 * @see https://refactoring.guru/design-patterns/facade Pattern: Facade
 */
final class AliasLoader
{
    /**
     * @var array<string, class-string> Map of alias => target class
     */
    private array $aliases = [];

    /**
     * @var bool Whether aliases have been registered
     */
    private bool $registered = false;

    /**
     * Register a class alias.
     *
     * @param string $alias Short name (e.g., 'Route')
     * @param class-string $class Full class name (e.g., 'Delirium\Http\Router')
     *
     * @throws InvalidArgumentException If target class does not exist
     */
    public function alias(string $alias, string $class): void
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new InvalidArgumentException(
                "Alias target class [{$class}] does not exist"
            );
        }

        $this->aliases[$alias] = $class;
    }

    /**
     * Register all aliases using class_alias().
     *
     * This should be called during application boot, after providers are registered
     * but before boot() is called.
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        foreach ($this->aliases as $alias => $class) {
            if (class_exists($alias) || interface_exists($alias)) {
                throw new InvalidArgumentException(
                    "Cannot alias [{$alias}] as it already exists as a class or interface"
                );
            }

            class_alias($class, $alias);
        }

        $this->registered = true;
    }

    /**
     * Get all registered aliases (for testing/debugging).
     *
     * @return array<string, class-string>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Load aliases from a manifest array.
     *
     * @param array<string, class-string> $aliases
     */
    public function loadFromManifest(array $aliases): void
    {
        foreach ($aliases as $alias => $class) {
            $this->alias($alias, $class);
        }
    }
}
