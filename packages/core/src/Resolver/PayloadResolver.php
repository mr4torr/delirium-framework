<?php

declare(strict_types=1);

namespace Delirium\Core\Resolver;

use Delirium\Core\Hydrator\ObjectHydrator;
use Delirium\Http\Attribute\MapRequestPayload;
use Delirium\Http\Contract\ArgumentResolverInterface;
use Delirium\Http\Exception\ValidationException;
use Delirium\Validation\Contract\ValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

// use Delirium\Http\Exception\ClientException; // Need to create or use generic HttpException

class PayloadResolver implements ArgumentResolverInterface
{
    public function __construct(
        private ObjectHydrator $hydrator,
        private ?ValidatorInterface $validator = null,
    ) {}

    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool
    {
        $attributes = $parameter->getAttributes(MapRequestPayload::class);
        return $attributes !== [];
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        // 1. Decode Body
        // Assuming JSON for V1.
        $body = (string) $request->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Throw 400 Bad Request
            throw new RuntimeException('Invalid JSON payload: ' . json_last_error_msg());

            // Ideally: throw new BadRequestException(...)
        }

        if (!is_array($data)) {
            $data = []; // Or throw expected object?
        }

        // 2. Hydrate
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new RuntimeException('MapRequestPayload requires a typed class argument.');
        }

        $className = $type->getName();
        $dto = $this->hydrator->hydrate($className, $data);

        // 3. Validate
        if ($this->validator) {
            $errors = $this->validator->validate($dto);
            if ($errors !== []) {
                // Throw 422 Unprocessable Entity
                // We need a structured exception that Application can catch and format.
                throw new ValidationException($errors);
            }
        }

        return $dto;
    }
}
