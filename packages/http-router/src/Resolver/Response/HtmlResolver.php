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
use function is_array;
use function is_string;

class HtmlResolver implements ResponseResolverInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    public function supports(mixed $data, ServerRequestInterface $request, array $attributes): bool
    {
        $type = $attributes['type'] ?? null;
        if ($data instanceof PsrResponseInterface) {
            return $type === ResponseTypeEnum::HTML;
        }

        return $type === ResponseTypeEnum::HTML && (is_string($data) || is_array($data));
    }

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface
    {
        if ($data instanceof PsrResponseInterface) {
            if (!$data->hasHeader('Content-Type')) {
                $data = $data->withHeader('Content-Type', 'text/html');
            }

            return $data instanceof ResponseInterface ? $data : new Response($data, $this->streamFactory);
        }

        $status = isset($attributes['status']) ? (int) $attributes['status'] : 200;
        $psrResponse = $this->responseFactory->createResponse($status);
        $response = new Response($psrResponse, $this->streamFactory);

        if (is_array($data)) {
            $data = json_encode($data);
        }

        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withBody($this->streamFactory->createStream((string) $data));
    }
}
