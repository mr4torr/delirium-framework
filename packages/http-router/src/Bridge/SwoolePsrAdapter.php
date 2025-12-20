<?php

declare(strict_types=1);

namespace Delirium\Http\Bridge;

use Delirium\Http\Contract\ContextAdapterInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenSwoole\Http\Request as SwooleRequest;
use OpenSwoole\Http\Response as SwooleResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
// use Psr\Http\Message\UploadedFileInterface;
// use Psr\Http\Message\UriInterface;

class SwoolePsrAdapter implements ContextAdapterInterface
{
    private Psr17Factory $psr17Factory;

    public function __construct()
    {
        $this->psr17Factory = new Psr17Factory();
    }

    public function createFromSwoole(SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $server = $swooleRequest->server;
        $headers = $swooleRequest->header ?? [];
        $cookies = $swooleRequest->cookie ?? [];
        $files = $swooleRequest->files ?? [];
        $get = $swooleRequest->get ?? [];
        $post = $swooleRequest->post ?? [];
        $rawContent = $swooleRequest->getContent() ?: '';

        // Build URI
        $uri = $this->psr17Factory->createUri()
            ->withScheme(isset($server['https']) && $server['https'] !== 'off' ? 'https' : 'http')
            ->withHost($headers['host'] ?? 'localhost')
            ->withPort($server['server_port'] ?? 80)
            ->withPath($server['request_uri'] ?? '/')
            ->withQuery($server['query_string'] ?? '');

        // Create Request
        $request = $this->psr17Factory->createServerRequest(
            $server['request_method'] ?? 'GET',
            $uri,
            $server
        );

        // Headers
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        // Cookie Params
        $request = $request->withCookieParams($cookies);

        // Query Params
        $request = $request->withQueryParams($get);

        // Parsed Body (Post)
        $request = $request->withParsedBody($post);

        // Uploaded Files - Need mapping logic, skipping deep mapping for MVP, simple assignment if matching structure
        // Nyholm factory has createUploadedFile but we need to iterate $files
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            $uploadedFiles[$key] = $this->psr17Factory->createUploadedFile(
                $this->psr17Factory->createStreamFromFile($file['tmp_name']),
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type']
            );
        }
        $request = $request->withUploadedFiles($uploadedFiles);

        // Body Stream
        $stream = $this->psr17Factory->createStream($rawContent);
        $request = $request->withBody($stream);

        return $request;
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
