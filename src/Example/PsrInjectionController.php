<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\StreamInterface;

#[Controller]
class PsrInjectionController
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    #[Get('/psr-test')]
    public function index(ServerRequestInterface $request, Response $response, StreamInterface $stream): ResponseInterface
    {
        $hasContainer = $this->container instanceof ContainerInterface;
        $method = $request->getMethod();
    
        
        $response->withStatus(200);
        $stream->write("PSR Injection Works! Method: {$method}, Container Injected: " . ($hasContainer ? 'Yes' : 'No'));
        $response->withBody($stream);

        return $response;
    }
}
