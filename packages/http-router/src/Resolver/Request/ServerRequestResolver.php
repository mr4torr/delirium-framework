<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Request;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;

class ServerRequestResolver implements ArgumentResolverInterface
{
    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();
        if (!$type || !$type instanceof \ReflectionNamedType) {
            return false;
        }

        return is_a($type->getName(), ServerRequestInterface::class, true);
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        return $request;
    }
}
