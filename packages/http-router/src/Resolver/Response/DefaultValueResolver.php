<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Response;

use Delirium\Http\Contract\ResponseResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DefaultValueResolver implements ResponseResolverInterface
{
    public function __construct(
        private \Psr\Http\Message\ResponseFactoryInterface $responseFactory,
        private \Psr\Http\Message\StreamFactoryInterface $streamFactory
    ) {}

    public function supports(mixed $data, ServerRequestInterface $request, array $attributes): bool
    {
        return true;
    }

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface
    {
        if ($data instanceof \Psr\Http\Message\ResponseInterface) {
            // Check type default
            $type = $attributes['type'] ?? \Delirium\Http\Enum\ResponseTypeEnum::JSON;

            if ($type === \Delirium\Http\Enum\ResponseTypeEnum::JSON && !$data->hasHeader('Content-Type')) {
                 return $data->withHeader('Content-Type', 'application/json');
            }

             return $data instanceof ResponseInterface ? $data : new \Delirium\Http\Message\Response($data, $this->streamFactory);
        }

        $status = isset($attributes['status']) ? (int)$attributes['status'] : 200;
        $type = $attributes['type'] ?? \Delirium\Http\Enum\ResponseTypeEnum::JSON;

        $psrResponse = $this->responseFactory->createResponse($status);
        $response = new \Delirium\Http\Message\Response($psrResponse, $this->streamFactory);

        if ($type === \Delirium\Http\Enum\ResponseTypeEnum::RAW) {
            return $response->withBody(
                $this->streamFactory->createStream((string)$this->content($data, false))
            );
        }

        // JSON
        // We rely on json_encode to handle scalars too (e.g. 1 -> 1, "foo" -> "foo" (quoted))
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                $this->streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            );
    }

    private function content(mixed $body, bool $json): string
    {
        if (is_array($body) || is_object($body)) {
             if ($json) {
                return json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
             }
             // For raw, array/object to string is usually "Array" or Error.
             // We'll try json_encode for raw array/object too just to be useful, or cast.
             return json_encode($body);
        }

        if ($body === null) {
            return '';
        }

        if (is_bool($body)) {
            return $body ? 'true' : 'false';
        }

        return (string) $body;
    }
}
