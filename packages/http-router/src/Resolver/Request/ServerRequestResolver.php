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

        $name = $type->getName();
        return (
            is_a($name, ServerRequestInterface::class, true)
            || is_a($name, \Delirium\Http\Contract\RequestInterface::class, true)
        );
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        if (!$request instanceof \Delirium\Http\Message\Request) {
            return new \Delirium\Http\Message\Request($request);
        }
        return $request;
    }
}
