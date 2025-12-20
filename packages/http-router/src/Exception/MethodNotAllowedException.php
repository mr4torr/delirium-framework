<?php

declare(strict_types=1);

namespace Delirium\Http\Exception;

use RuntimeException;

class MethodNotAllowedException extends RuntimeException
{
    public function __construct(string $message = "Method not allowed", int $code = 405)
    {
        parent::__construct($message, $code);
    }
}
