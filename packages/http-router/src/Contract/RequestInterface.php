<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

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
