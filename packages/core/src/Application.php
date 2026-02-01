<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Contract\ApplicationInterface;
use Delirium\Core\Foundation\AliasLoader;
use Delirium\Core\Foundation\ProviderRepository;
use Delirium\Core\Options\CorsOptions;
use Delirium\Core\Options\ServerOptions;
use Delirium\Http\Contract\ContextAdapterInterface;
use Delirium\Http\Contract\RouterInterface;
use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Delirium\Http\Exception\ValidationException;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Throwable;

class Application implements ApplicationInterface
{
    private ?Server $server = null;
    private ServerOptions $serverOptions;
    private ProviderRepository $providerRepository;
    private AliasLoader $aliasLoader;
    private string $environment = 'prod';
    private bool $booted = false;

    public function __construct(
        private ContainerInterface $container,
        private AppOptions $options,
        private RouterInterface $router,
        private ContextAdapterInterface $adapter,
        ?string $environment = null,
    ) {
        $opts = $options->get(ServerOptions::class);
        $this->serverOptions = $opts instanceof ServerOptions ? $opts : new ServerOptions();

        $this->environment = $environment ?? getenv('APP_ENV') ?: 'prod';

        // Initialize provider repository and alias loader
        $cacheFile = __DIR__ . '/../../var/cache/discovery.php';
        $this->providerRepository = new ProviderRepository($container, $cacheFile, $this->environment);
        $this->aliasLoader = new AliasLoader();
    }

    public function listen(int $port = 9501, string $host = '0.0.0.0'): void
    {
        // Boot providers before starting server
        $this->boot();

        $this->server = new Server($host, $port, $this->serverOptions->mode);
        $this->configureServer($this->server);
        $this->server->start();
    }

    public function shutdown(): void
    {
        $this->server?->shutdown();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Register a service provider for a specific environment.
     *
     * @param class-string<\Delirium\Support\ServiceProvider> $provider
     * @param string $env Environment tag: 'all', 'dev', or 'prod'
     *
     * @throws InvalidArgumentException If provider class does not exist
     */
    public function register(string $provider, string $env = 'all'): self
    {
        $this->providerRepository->register($provider, $env);
        return $this;
    }

    /**
     * Register a class alias.
     *
     * @param string $alias Short name (e.g., 'Route')
     * @param class-string $class Full class name (e.g., 'Delirium\Http\Router')
     *
     * @throws InvalidArgumentException If target class does not exist
     */
    public function alias(string $alias, string $class): self
    {
        $this->aliasLoader->alias($alias, $class);
        return $this;
    }

    /**
     * Boot the application: load providers, register aliases, and call boot().
     *
     * This is called automatically before listen() starts the server.
     */
    private function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Load providers (instantiate and call register())
        $this->providerRepository->load();

        // Load aliases from cache if available
        $cachedAliases = $this->providerRepository->getAliasesFromCache();
        if ($cachedAliases !== []) {
            $this->aliasLoader->loadFromManifest($cachedAliases);
        }

        // Register aliases
        $this->aliasLoader->register();

        // Boot providers
        $this->providerRepository->boot();

        $this->booted = true;
    }

    private function configureServer(Server $server): void
    {
        $server->set($this->serverOptions->toArray());
        $server->on('Start', static function (Server $server) {
            echo "[Swoole] Http server started at http://{$server->host}:{$server->port}\n";
        });

        $server->on('Request', function (Request $request, Response $response) {
            $this->handleRequest($request, $response);
        });
    }

    private function handleRequest(Request $request, Response $response): void
    {
        // Handle CORS
        $corsOptions = $this->options->get(CorsOptions::class);
        if ($corsOptions) {
            $this->applyCorsHeaders($response, $corsOptions);
            if ($request->server['request_method'] === 'OPTIONS') {
                $response->status(204);
                $response->end();
                return;
            }
        }

        // Dispatch to Router
        try {
            // Adapter is injected

            $psrRequest = $this->adapter->createFromSwoole($request);

            $result = $this->router->dispatch($psrRequest);

            // Handle Result
            // Result could be ResponseInterface or string or anything (RouterInterface returns mixed)
            // If ResponseInterface, emit it.

            if ($result instanceof ResponseInterface) {
                $this->adapter->emitToSwoole($result, $response);
                return;
            }

            if (is_string($result)) {
                $response->end($result);
                return;
            }

            $response->header('Content-Type', 'application/json');
            $response->end(json_encode($result));
        } catch (RouteNotFoundException $e) {
            $response->status(404);
            $response->end('Not Found');
        } catch (MethodNotAllowedException $e) {
            $response->status(405);
            $response->end('Method Not Allowed');
        } catch (ValidationException $e) {
            $response->status($e->getCode());
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode($e));
        } catch (Throwable $e) {
            $response->status(500);
            $response->end('Internal Server Error: ' . $e->getMessage());
        }
    }

    private function applyCorsHeaders(Response $response, CorsOptions $cors): void
    {
        $response->header('Access-Control-Allow-Origin', implode(', ', $cors->allowOrigins));
        $response->header('Access-Control-Allow-Methods', implode(', ', $cors->allowMethods));
        $response->header('Access-Control-Allow-Headers', implode(', ', $cors->allowHeaders));
    }
}
