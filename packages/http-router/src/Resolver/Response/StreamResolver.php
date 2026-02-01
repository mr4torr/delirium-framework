<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Response;

use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Contract\ResponseResolverInterface;
use Delirium\Http\Enum\ResponseTypeEnum;
use Delirium\Http\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamResolver implements ResponseResolverInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function supports(mixed $data, ServerRequestInterface $request, array $attributes): bool
    {
        $type = $attributes['type'] ?? null;
        if ($data instanceof PsrResponseInterface) {
            return $type === ResponseTypeEnum::STREAM;
        }
        return $type === ResponseTypeEnum::STREAM || $data instanceof StreamInterface || is_resource($data);
    }

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface
    {
        if ($data instanceof PsrResponseInterface) {
            // For stream, we generally don't enforce Content-Type unless we know it.
            // But valid response is valid.
            return $data instanceof ResponseInterface ? $data : new Response($data, $this->streamFactory);
        }

        $status = isset($attributes['status']) ? (int) $attributes['status'] : 200;
        $psrResponse = $this->responseFactory->createResponse($status);
        $response = new Response($psrResponse, $this->streamFactory);

        if ($data instanceof StreamInterface) {
            return $response->withBody($data);
        }

        if (is_resource($data)) {
            return $response->withBody($this->streamFactory->createStreamFromResource($data));
        }

        // Should not happen if supports check works, but as logic fallback
        return $response;
    }
}
