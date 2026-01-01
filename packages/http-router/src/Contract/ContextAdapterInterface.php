<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ContextAdapterInterface
{
    public function createFromSwoole(SwooleRequest $swooleRequest): ServerRequestInterface;

    public function emitToSwoole(ResponseInterface $psrResponse, SwooleResponse $swooleResponse): void;
}
