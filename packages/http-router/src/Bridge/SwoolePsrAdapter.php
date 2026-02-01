<?php

declare(strict_types=1);

namespace Delirium\Http\Bridge;

use Delirium\Http\Contract\ContextAdapterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class SwoolePsrAdapter implements ContextAdapterInterface
{
    public function __construct(
        private ServerRequestFactoryInterface $serverRequestFactory,
        private UriFactoryInterface $uriFactory,
        private StreamFactoryInterface $streamFactory,
        private UploadedFileFactoryInterface $uploadedFileFactory,
    ) {}

    public function createFromSwoole(SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $server = $swooleRequest->server;
        $headers = $swooleRequest->header ?? [];
        $cookies = $swooleRequest->cookie ?? [];
        $files = $swooleRequest->files ?? [];
        $get = $swooleRequest->get ?? [];
        $post = $swooleRequest->post ?? [];
        $content = $swooleRequest->getContent();
        $rawContent = $content !== false ? $content : '';

        // Build URI
        $uri = $this->uriFactory
            ->createUri()
            ->withScheme(isset($server['https']) && $server['https'] !== 'off' ? 'https' : 'http')
            ->withHost($headers['host'] ?? 'localhost')
            ->withPort($server['server_port'] ?? 80)
            ->withPath($server['request_uri'] ?? '/')
            ->withQuery($server['query_string'] ?? '');

        // Create Request
        $request = $this->serverRequestFactory->createServerRequest($server['request_method'] ?? 'GET', $uri, $server);

        // ... headers ...
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Cookie Params
        $request = $request->withCookieParams($cookies);

        // Query Params
        $request = $request->withQueryParams($get);

        // Parsed Body (Post)
        $request = $request->withParsedBody($post);

        // Uploaded Files
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            $uploadedFiles[$key] = $this->uploadedFileFactory->createUploadedFile(
                $this->streamFactory->createStreamFromFile($file['tmp_name']),
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type'],
            );
        }
        $request = $request->withUploadedFiles($uploadedFiles);

        // Body Stream
        $stream = $this->streamFactory->createStream($rawContent);
        return $request->withBody($stream);
    }

    public function emitToSwoole(ResponseInterface $psrResponse, SwooleResponse $swooleResponse): void
    {
        // Status
        $swooleResponse->status($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());

        // Headers
        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header((string) $name, $value);
            }
        }

        // Body
        $body = $psrResponse->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        $swooleResponse->end($body->getContents());
    }
}
