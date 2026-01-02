<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Response;

use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Contract\ResponseResolverInterface;
use Delirium\Http\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class JsonResolver implements ResponseResolverInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {}

    public function supports(mixed $data, ServerRequestInterface $request, array $attributes): bool
    {
        $type = $attributes['type'] ?? null;

        if ($data instanceof \Psr\Http\Message\ResponseInterface) {
            return $type === \Delirium\Http\Enum\ResponseTypeEnum::JSON;
        }

        if ($type !== null && $type !== \Delirium\Http\Enum\ResponseTypeEnum::JSON) {
            return false;
        }

        return $type === \Delirium\Http\Enum\ResponseTypeEnum::JSON || $type === null || is_array($data) || is_object($data) || $data instanceof \JsonSerializable;
    }

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface
    {
        if ($data instanceof \Psr\Http\Message\ResponseInterface) {
            if (!$data->hasHeader('Content-Type')) {
                 return $data->withHeader('Content-Type', 'application/json');
            }
            // Ensure Delirium Response interface?
            // If it's pure PSR7, wrapper might be needed if return type is strict Delirium Response.
            // But interface is Delirium... which extends PSR.
            // If strict typing requires Delirium\Contract\ResponseInterface, we might need to wrap or ensure it complies.
            // Assuming compatibility for now or user uses Delirium Response.
            return $data instanceof ResponseInterface ? $data : new Response($data, $this->streamFactory);
        }

        $status = isset($attributes['status']) ? (int)$attributes['status'] : 200;
        $psrResponse = $this->responseFactory->createResponse($status);
        $response = new Response($psrResponse, $this->streamFactory);

        return $response->json($data);
    }
}
