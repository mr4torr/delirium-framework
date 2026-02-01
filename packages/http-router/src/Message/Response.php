<?php

declare(strict_types=1);

namespace Delirium\Http\Message;

use Delirium\Http\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use SimpleXMLElement;

class Response implements ResponseInterface
{
    public function __construct(
        private PsrResponseInterface $psrResponse,
        private \Psr\Http\Message\StreamFactoryInterface $streamFactory,
    ) {}

    // --- PSR-7 Proxy Methods ---

    public function getStatusCode(): int
    {
        return $this->psrResponse->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->psrResponse = $this->psrResponse->withStatus($code, $reasonPhrase);
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->psrResponse->getReasonPhrase();
    }

    public function getProtocolVersion(): string
    {
        return $this->psrResponse->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->psrResponse = $this->psrResponse->withProtocolVersion($version);
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->psrResponse->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->psrResponse->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->psrResponse->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->psrResponse->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->psrResponse = $this->psrResponse->withHeader($name, $value);
        return $new;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->psrResponse = $this->psrResponse->withAddedHeader($name, $value);
        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        $new->psrResponse = $this->psrResponse->withoutHeader($name);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->psrResponse->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->psrResponse = $this->psrResponse->withBody($body);
        return $new;
    }

    // --- Helper Methods ---

    public function json(mixed $data): self
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->withHeader('Content-Type', 'application/json')->withBody($this->streamFactory->createStream(
            $json,
        ));
    }

    public function xml(mixed $data): self
    {
        // Basic serialization for simple arrays/objects. For complex usage, users should return explicit content.
        // If data is string, assume it's XML.
        if (is_string($data)) {
            return $this->withHeader('Content-Type', 'application/xml')->withBody($this->streamFactory->createStream(
                $data,
            ));
        }

        // Simple recursive array to XML
        $xml = new SimpleXMLElement('<root/>');
        $toXml = static function ($data, SimpleXMLElement &$xmlData) use (&$toXml) {
            foreach ($data as $key => $value) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; //binding to generic key name
                }
                if (is_array($value) || is_object($value)) {
                    $subnode = $xmlData->addChild($key);
                    $toXml($value, $subnode);
                    continue;
                }

                $xmlData->addChild((string) $key, htmlspecialchars((string) $value));
            }
        };
        $arrayData = is_object($data) ? get_object_vars($data) : (array) $data;
        $toXml($arrayData, $xml);

        return $this->withHeader(
            'Content-Type',
            'application/xml',
        )->withBody($this->streamFactory->createStream($xml->asXML()));
    }

    public function raw(string $data): self
    {
        return $this->withBody($this->streamFactory->createStream($data));
    }

    public function redirect(string $url, int $status = 302): self
    {
        return $this->withStatus($status)->withHeader('Location', $url);
    }

    public function download(string $file, string $name = ''): self
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new RuntimeException("File not found: {$file}");
        }

        $filename = $name !== '' ? $name : basename($file);
        $stream = $this->streamFactory->createStreamFromFile($file);

        return $this
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withBody($stream);
    }

    public function withCookie(string $key, string $value): self
    {
        // Simple Set-Cookie implementation
        // For advanced options (expiry, path), user might need logic, but this matches basic spec req
        return $this->withAddedHeader('Set-Cookie', "{$key}={$value}");
    }

    public function gzip(): self
    {
        $body = $this->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        $contents = $body->getContents();
        $compressed = gzencode($contents);

        return $this->withHeader('Content-Encoding', 'gzip')->withBody($this->streamFactory->createStream($compressed));
    }
}
