<?php

declare(strict_types=1);

namespace Delirium\Http\Bridge;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;

class SwooleStream implements StreamInterface, Stringable
{
    private $stream;
    
    /** 
     * @param string|resource $body
     */
    public function __construct(mixed $body = '')
    {
        // Swoole raw content is usually a string.
        if (is_string($body)) {
            $this->stream = fopen('php://temp', 'r+');
            fwrite($this->stream, $body);
            rewind($this->stream);
        } elseif (is_resource($body)) {
            $this->stream = $body;
        } else {
             $this->stream = fopen('php://temp', 'r+');
        }
    }

    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }
            return $this->getContents();
        } catch (RuntimeException) {
            return '';
        }
    }

    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->detach();
    }

    public function detach()
    {
        $result = $this->stream;
        $this->stream = null;
        return $result;
    }

    public function getSize(): ?int
    {
        if (!is_resource($this->stream)) {
            return null;
        }
        $stats = fstat($this->stream);
        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        if (!is_resource($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        $result = ftell($this->stream);
        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }
        return $result;
    }

    public function eof(): bool
    {
        return !$this->stream || feof($this->stream);
    }

    public function isSeekable(): bool
    {
        if (!is_resource($this->stream)) {
            return false;
        }
        $meta = stream_get_meta_data($this->stream);
        return $meta['seekable'];
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . $whence);
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        if (!is_resource($this->stream)) {
            return false;
        }
        $meta = stream_get_meta_data($this->stream);
        return str_contains($meta['mode'], 'x') || str_contains($meta['mode'], 'w') || str_contains($meta['mode'], 'c') || str_contains($meta['mode'], 'a') || str_contains($meta['mode'], '+');
    }

    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable');
        }
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }
        return $result;
    }

    public function isReadable(): bool
    {
        if (!is_resource($this->stream)) {
            return false;
        }
        $meta = stream_get_meta_data($this->stream);
        return str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+');
    }

    public function read(int $length): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }
        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new RuntimeException('Unable to read from stream');
        }
        return $result;
    }

    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }
        $result = stream_get_contents($this->stream);
        if ($result === false) {
            throw new RuntimeException('Unable to read stream contents');
        }
        return $result;
    }

    public function getMetadata(?string $key = null)
    {
        if (!is_resource($this->stream)) {
            return $key ? null : [];
        }
        $meta = stream_get_meta_data($this->stream);
        return $key ? ($meta[$key] ?? null) : $meta;
    }
}
