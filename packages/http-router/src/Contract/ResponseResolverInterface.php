<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseResolverInterface
{
    public function supports(mixed $data, ServerRequestInterface $request, array $attributes): bool;

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface;
}
