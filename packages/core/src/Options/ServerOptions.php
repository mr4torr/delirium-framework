<?php

declare(strict_types=1);

namespace Delirium\Core\Options;

class ServerOptions
{
    public function __construct(
        public readonly int $mode = SWOOLE_BASE,
        public readonly int $workerNum = 4,
        public readonly int $maxRequest = 10000,
        public readonly bool $daemonize = false,
        public readonly int $reactorNum = 2,
        public readonly int $maxConnection = 1024,
        public readonly int $packageMaxLength = 2 * 1024 * 1024,
        public readonly bool $enableReusePort = true,
    ) {
    }

    public function toArray(): array
    {
        return [
            'worker_num' => $this->workerNum,
            'max_request' => $this->maxRequest,
            'daemonize' => $this->daemonize,
            'reactor_num' => $this->reactorNum,
            'max_connection' => $this->maxConnection,
            'package_max_length' => $this->packageMaxLength,
            'enable_reuse_port' => $this->enableReusePort,
        ];
    }
}
