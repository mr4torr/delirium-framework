<?php

namespace Delirium\Http\Contract;

use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\UploadedFileInterface;
use Delirium\Http\Contract\ResponseResolverInterface;

interface RequestInterface extends PsrServerRequestInterface
{
    public function input(string $key, mixed $default = null): mixed;
    public function all(?array $keys = null): array;
    public function query(string $key, mixed $default = null): mixed;
    public function post(string $key, mixed $default = null): mixed;
    public function header(string $key, string $default = ''): string;
    public function file(string $key): ?UploadedFileInterface;
    public function has(string $key): bool;
    public function cookie(string $key, mixed $default = null): mixed;
}

interface ResponseInterface extends PsrResponseInterface
{
    public function json(mixed $data): self;
    public function xml(mixed $data): self;
    public function raw(string $data): self;
    public function redirect(string $url, int $status = 302): self;
    public function download(string $file, string $name = ''): self;
    public function withCookie(string $key, string $value): self;
}
