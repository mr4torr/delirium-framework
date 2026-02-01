<?php

declare(strict_types=1);

namespace Delirium\Http\Message;

use Delirium\Http\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    public function __construct(
        private ServerRequestInterface $psrRequest,
    ) {}

    // --- PSR-7 Proxy Methods ---

    public function getProtocolVersion(): string
    {
        return $this->psrRequest->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withProtocolVersion($version);
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->psrRequest->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->psrRequest->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->psrRequest->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->psrRequest->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withHeader($name, $value);
        return $new;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withAddedHeader($name, $value);
        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withoutHeader($name);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->psrRequest->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withBody($body);
        return $new;
    }

    public function getRequestTarget(): string
    {
        return $this->psrRequest->getRequestTarget();
    }

    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withRequestTarget($requestTarget);
        return $new;
    }

    public function getMethod(): string
    {
        return $this->psrRequest->getMethod();
    }

    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withMethod($method);
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->psrRequest->getUri();
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withUri($uri, $preserveHost);
        return $new;
    }

    public function getServerParams(): array
    {
        return $this->psrRequest->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->psrRequest->getCookieParams();
    }

    public function withCookieParams(array $cookies): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withCookieParams($cookies);
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->psrRequest->getQueryParams();
    }

    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withQueryParams($query);
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->psrRequest->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withUploadedFiles($uploadedFiles);
        return $new;
    }

    public function getParsedBody(): null|array|object
    {
        return $this->psrRequest->getParsedBody();
    }

    public function withParsedBody($data): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withParsedBody($data);
        return $new;
    }

    public function getAttributes(): array
    {
        return $this->psrRequest->getAttributes();
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->psrRequest->getAttribute($name, $default);
    }

    public function withAttribute(string $name, $value): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withAttribute($name, $value);
        return $new;
    }

    public function withoutAttribute(string $name): static
    {
        $new = clone $this;
        $new->psrRequest = $this->psrRequest->withoutAttribute($name);
        return $new;
    }

    // --- Helper Methods ---

    public function input(string $key, mixed $default = null): mixed
    {
        $body = $this->getParsedBody();
        $source = is_array($body) ? $body : (array) $body;

        if (array_key_exists($key, $source)) {
            return $source[$key];
        }

        $query = $this->getQueryParams();
        if (array_key_exists($key, $query)) {
            return $query[$key];
        }

        return $default;
    }

    public function all(?array $keys = null): array
    {
        $body = $this->getParsedBody();
        $bodyParams = is_array($body) ? $body : (array) $body;
        $queryParams = $this->getQueryParams();

        $all = array_merge($queryParams, $bodyParams);

        if (null === $keys) {
            return $all;
        }

        $results = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $all)) {
                continue;
            }

            $results[$key] = $all[$key];
        }

        return $results;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        $query = $this->getQueryParams();
        return array_key_exists($key, $query) ? $query[$key] : $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        $body = $this->getParsedBody();
        $source = is_array($body) ? $body : (array) $body;
        return array_key_exists($key, $source) ? $source[$key] : $default;
    }

    public function header(string $key, string $default = ''): string
    {
        if (!$this->hasHeader($key)) {
            return $default;
        }
        return $this->getHeaderLine($key);
    }

    public function file(string $key): ?UploadedFileInterface
    {
        $files = $this->getUploadedFiles();
        return $files[$key] ?? null;
    }

    public function has(string $key): bool
    {
        $body = $this->getParsedBody();
        $bodyParams = is_array($body) ? $body : (array) $body;
        if (array_key_exists($key, $bodyParams)) {
            return true;
        }

        return array_key_exists($key, $this->getQueryParams());
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        $cookies = $this->getCookieParams();
        return array_key_exists($key, $cookies) ? $cookies[$key] : $default;
    }
}
