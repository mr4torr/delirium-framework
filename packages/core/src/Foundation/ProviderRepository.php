<?php

declare(strict_types=1);

namespace Delirium\Core\Foundation;

use Delirium\Support\ServiceProvider;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Registry for Service Providers with environment-specific loading and caching.
 *
 * This class manages the lifecycle of service providers:
 * 1. Registration: Collect provider class names with environment tags
 * 2. Loading: Instantiate and call register() on all applicable providers
 * 3. Booting: Call boot() on all loaded providers
 * 4. Caching: Persist the manifest to disk for fast subsequent boots
 *
 * @see https://refactoring.guru/design-patterns/registry Pattern: Registry
 */
final class ProviderRepository
{
    /**
     * @var list<ServiceProvider> Instantiated provider instances
     */
    private array $loadedProviders = [];

    /**
     * @var bool Whether providers have been loaded
     */
    private bool $loaded = false;

    /**
     * @param ContainerInterface $container The DI container
     * @param string $cacheFile Absolute path to cache file (e.g., var/cache/discovery.php)
     * @param string $currentEnv Current environment (dev/prod)
     * @param array<string, list<class-string<ServiceProvider>>> $providers Map of environment => provider class names
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $cacheFile,
        private readonly string $currentEnv = 'prod',
        private array $providers = [
            'all' => [
                \Delirium\Http\HttpRouterServiceProvider::class,
            ],
            'dev' => [],
            'prod' => [],
        ]
    ) {
    }

    /**
     * Register a service provider for a specific environment.
     *
     * @param class-string<ServiceProvider> $providerClass
     * @param string $env Environment tag: 'all', 'dev', or 'prod'
     *
     * @throws InvalidArgumentException If provider class does not exist
     */
    public function register(string $providerClass, string $env = 'all'): void
    {
        if (!class_exists($providerClass)) {
            throw new InvalidArgumentException(
                "Provider class [{$providerClass}] does not exist"
            );
        }

        if (!isset($this->providers[$env])) {
            $this->providers[$env] = [];
        }

        if (!in_array($providerClass, $this->providers[$env], true)) {
            $this->providers[$env][] = $providerClass;
        }
    }

    /**
     * Load providers from cache or manifest, instantiate, and call register().
     *
     * This should be called during application boot, before listen().
     */
    public function load(): void
    {
        if ($this->loaded) {
            return;
        }

        // Attempt to load from cache
        $manifest = $this->loadFromCache();

        if ($manifest === null) {
            // No cache, use the registered manifest
            $manifest = $this->providers;
        }

        // Determine which providers to load for current environment
        $providersToLoad = array_merge(
            $manifest['all'] ?? [],
            $manifest[$this->currentEnv] ?? []
        );

        // Instantiate each provider and call register()
        foreach ($providersToLoad as $providerClass) {
            if (!class_exists($providerClass)) {
                throw new InvalidArgumentException(
                    "Provider class [{$providerClass}] does not exist"
                );
            }

            $provider = new $providerClass($this->container);
            $this->loadedProviders[] = $provider;
            $provider->register();
        }

        $this->loaded = true;
    }

    /**
     * Call boot() on all loaded providers.
     *
     * This should be called after load() and after all providers are registered.
     */
    public function boot(): void
    {
        foreach ($this->loadedProviders as $provider) {
            $provider->boot();
        }
    }

    /**
     * Persist the current provider manifest to cache.
     *
     * This should be called after registration is complete, typically in production.
     *
     * @param array<string, class-string> $aliases Optional alias map to include in cache
     */
    public function cache(array $aliases = []): void
    {
        $cacheDir = dirname($this->cacheFile);

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0o755, true);
        }

        $manifest = [
            'providers' => $this->providers,
            'aliases' => $aliases,
        ];

        $content = '<?php' . PHP_EOL . PHP_EOL;
        $content .= 'declare(strict_types=1);' . PHP_EOL . PHP_EOL;
        $content .= 'return ' . var_export($manifest, true) . ';' . PHP_EOL;

        file_put_contents($this->cacheFile, $content);
    }

    /**
     * Load provider manifest from cache file.
     *
     * @return array<string, list<class-string<ServiceProvider>>>|null
     */
    private function loadFromCache(): ?array
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }

        $manifest = require $this->cacheFile;

        if (!is_array($manifest)) {
            return null;
        }

        // Support both old format (direct providers array) and new format (with providers + aliases)
        if (isset($manifest['providers'])) {
            return $manifest['providers'];
        }

        return $manifest;
    }

    /**
     * Get aliases from cache file.
     *
     * @return array<string, class-string>
     */
    public function getAliasesFromCache(): array
    {
        if (!file_exists($this->cacheFile)) {
            return [];
        }

        $manifest = require $this->cacheFile;

        if (!is_array($manifest) || !isset($manifest['aliases'])) {
            return [];
        }

        return $manifest['aliases'];
    }

    /**
     * Get the list of registered providers (for testing/debugging).
     *
     * @return array<string, list<class-string<ServiceProvider>>>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
