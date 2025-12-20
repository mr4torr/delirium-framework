<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use OpenSwoole\Http\Request as SwooleRequest;
use OpenSwoole\Http\Response as SwooleResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ContextAdapterInterface
{
    public function createFromSwoole(SwooleRequest $swooleRequest): ServerRequestInterface;

    public function emitToSwoole(ResponseInterface $psrResponse, SwooleResponse $swooleResponse): void;
}
