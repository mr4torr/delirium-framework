<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Response;

use Delirium\Http\Contract\ResponseResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Stream;

class DefaultValueResolver implements ResponseResolverInterface
{
    public function supports(ResponseInterface $response, mixed $results): bool
    {
        return true;
    }

    public function resolve(ResponseInterface $response, mixed $results): mixed
    {
        return $response
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody(
                Stream::create($this->content($results))
            );
    }

    private function content(mixed $body): string
    {
        if (is_array($body) || is_object($body)) {
            return json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($body === null) {
            return '';
        }

        if (is_bool($body)) {
            return $body ? '1' : '0';
        }

        return (string) $body;
    }
}
