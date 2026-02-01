<?php

declare(strict_types=1);

namespace Delirium\Http\Exception;

use RuntimeException;

class RouteNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Route not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
