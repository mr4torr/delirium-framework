<?php

require __DIR__ . '/../vendor/autoload.php';

use Delirium\Http\Bridge\SwoolePsrAdapter;
use Delirium\Http\Router;
use Delirium\Http\RouteRegistry;
use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

// 1. Setup Router
$router = new Router(new RouteRegistry());
echo "Scanning controllers...\n";
$router->scan(__DIR__ . '/Controllers');

// 2. Setup Bridge
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$adapter = new SwoolePsrAdapter($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

// 3. Setup OpenSwoole Server
$server = new Server('127.0.0.1', 9501);

$server->on('Start', function (Server $server) {
    echo "OpenSwoole http server is started at http://127.0.0.1:9501\n";
    echo "Try: curl http://127.0.0.1:9501/api/hello\n";
    echo "Try: curl http://127.0.0.1:9501/api/greet/Mailon\n";
});

$server->on('Request', function (Request $request, Response $response) use ($router, $adapter) {
    try {
        // Convert to PSR-7
        $psrRequest = $adapter->createFromSwoole($request);
        
        // Dispatch
        $result = $router->dispatch($psrRequest);
        
        // Handle Response (Simple string handling for example)
        if ($result instanceof \Psr\Http\Message\ResponseInterface) {
            $psrResponse = $result;
        } else {
            $content = is_string($result) ? $result : json_encode($result);
            $psrResponse = new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], $content);
        }

        // Emit back to Swoole
        $adapter->emitToSwoole($psrResponse, $response);

    } catch (\Delirium\Http\Exception\RouteNotFoundException $e) {
        $response->status(404);
        $response->end(json_encode(['error' => 'Not Found']));
    } catch (\Throwable $e) {
        $response->status(500);
        $response->end(json_encode(['error' => $e->getMessage()]));
    }
});

$server->start();
