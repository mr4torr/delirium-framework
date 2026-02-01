<?php

declare(strict_types=1);

namespace Delirium\Http\Exception;

use JsonSerializable;
use RuntimeException;

class ValidationException extends RuntimeException implements JsonSerializable
{
    public function __construct(
        private array $errors,
        string $message = 'Bad Request',
        int $code = 400,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function jsonSerialize(): array
    {
        return [
            'statusCode' => $this->getCode(),
            'code' => 'VALIDATION_FIELDS',
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ];
    }
}
