<?php

declare(strict_types=1);

namespace Delirium\Core\Options;

class CorsOptions
{
    /**
     * @param string[] $allowOrigins
     * @param string[] $allowMethods
     * @param string[] $allowHeaders
     */
    public function __construct(
        public array $allowOrigins = ['*'],
        public array $allowMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        public array $allowHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
    ) {
    }
}
