<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Request;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;

class RouteParameterResolver implements ArgumentResolverInterface
{
    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool
    {
        // Check if the parameter name exists in the request attributes (route params are stored as attributes)
        return $request->getAttribute($parameter->getName()) !== null;
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        return $request->getAttribute($parameter->getName());
    }
}
