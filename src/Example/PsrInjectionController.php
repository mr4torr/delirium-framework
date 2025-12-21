<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\Http\Attribute\Get;
use Delirium\Http\Attribute\Controller;
use Delirium\Core\Http\Response;
use Delirium\Core\Http\JsonResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class PsrInjectionController
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    #[Get('/psr-response')]
    public function index(ServerRequestInterface $request, Response $response): ResponseInterface
    {
        $hasContainer = $this->container instanceof ContainerInterface;
        $method = $request->getMethod();

        return $response->body([
            'teste' => 'PSR Injection Works! Method: ' . $method . ', Container Injected: ' . ($hasContainer ? 'Yes' : 'No')
        ]);
    }

    #[Get('/psr-json')]
    public function indexJson(ServerRequestInterface $request, JsonResponse $response): ResponseInterface
    {
        $hasContainer = $this->container instanceof ContainerInterface;
        $method = $request->getMethod();
        $queryParams = $request->getQueryParams();

        return $response->body([
            'teste' => 'PSR Injection Works! Method: ' . $method . ', Container Injected: ' . ($hasContainer ? 'Yes' : 'No'),
            ...$queryParams
        ]);
    }
}
