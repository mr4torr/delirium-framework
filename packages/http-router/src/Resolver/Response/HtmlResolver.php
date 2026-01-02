<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Response;

use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Contract\ResponseResolverInterface;
use Delirium\Http\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HtmlResolver implements ResponseResolverInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {}

    public function supports(mixed $data, ServerRequestInterface $request, array $attributes): bool
    {
        $type = $attributes['type'] ?? null;
        if ($data instanceof \Psr\Http\Message\ResponseInterface) {
            return $type === \Delirium\Http\Enum\ResponseTypeEnum::HTML;
        }
        return $type === \Delirium\Http\Enum\ResponseTypeEnum::HTML && is_string($data);
    }

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface
    {
         if ($data instanceof \Psr\Http\Message\ResponseInterface) {
            if (!$data->hasHeader('Content-Type')) {
                 return $data->withHeader('Content-Type', 'text/html');
            }
            return $data instanceof ResponseInterface ? $data : new Response($data, $this->streamFactory);
         }

         $status = isset($attributes['status']) ? (int)$attributes['status'] : 200;
         $psrResponse = $this->responseFactory->createResponse($status);
         $response = new Response($psrResponse, $this->streamFactory);

         return $response
             ->withHeader('Content-Type', 'text/html')
             ->withBody($this->streamFactory->createStream((string)$data));
    }
}
