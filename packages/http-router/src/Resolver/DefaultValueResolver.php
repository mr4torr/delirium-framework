<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;

class DefaultValueResolver implements ArgumentResolverInterface
{
    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool
    {
        return $parameter->isDefaultValueAvailable();
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        return $parameter->getDefaultValue();
    }
}
