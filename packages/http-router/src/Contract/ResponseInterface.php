<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface extends PsrResponseInterface
{
    public function json(mixed $data): self;

    public function xml(mixed $data): self;

    public function raw(string $data): self;

    public function redirect(string $url, int $status = 302): self;

    public function download(string $file, string $name = ''): self;

    public function withCookie(string $key, string $value): self;
}
