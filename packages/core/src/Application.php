<?php

declare(strict_types=1);

namespace Delirium\Core;

use Delirium\Core\Contract\ApplicationInterface;
use Delirium\Http\Contract\RouterInterface;
use Psr\Container\ContainerInterface;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Delirium\Core\Options\CorsOptions;
use Delirium\Core\Options\ServerOptions;
use Delirium\Http\Contract\ContextAdapterInterface;

class Application implements ApplicationInterface
{
    private ?Server $server = null;
    private ServerOptions $serverOptions;

    public function __construct(
        private ContainerInterface $container,
        private AppOptions $options,
        private RouterInterface $router,
        private ContextAdapterInterface $adapter
    ) {
        $this->serverOptions = $options->get(ServerOptions::class) ?: new ServerOptions();
    }

    public function listen(int $port = 9501, string $host = '0.0.0.0'): void
    {
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

    private function configureServer(Server $server): void
    {
        $server->set($this->serverOptions->toArray());
        $server->on('Start', function (Server $server) {
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

            if ($result instanceof \Psr\Http\Message\ResponseInterface) {
                $this->adapter->emitToSwoole($result, $response);
            } elseif (is_string($result)) {
                $response->end($result);
            } else {
                 $response->header('Content-Type', 'application/json');
                 $response->end(json_encode($result));
            }

        } catch (\Delirium\Http\Exception\RouteNotFoundException $e) {
            $response->status(404);
            $response->end('Not Found');
        } catch (\Delirium\Http\Exception\MethodNotAllowedException $e) {
             $response->status(405);
             $response->end('Method Not Allowed');
        } catch (\Delirium\Http\Exception\ValidationException $e) {
             $response->status($e->getCode());
             $response->header('Content-Type', 'application/json');
             $response->end(json_encode($e));
        } catch (\Throwable $e) {
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
