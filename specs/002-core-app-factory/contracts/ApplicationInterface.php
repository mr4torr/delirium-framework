<?php

namespace Delirium\Core\Contract;

interface ApplicationInterface
{
    public function listen(int $port = 9501, string $host = '0.0.0.0'): void;
    public function getContainer(): \Psr\Container\ContainerInterface;
}
