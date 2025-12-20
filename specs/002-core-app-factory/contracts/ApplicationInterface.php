<?php

namespace Delirium\Core\Contract;

interface ApplicationInterface
{
    public function listen(?int $port = null, ?string $host = null): void;
    public function getContainer(): \Psr\Container\ContainerInterface;
}
