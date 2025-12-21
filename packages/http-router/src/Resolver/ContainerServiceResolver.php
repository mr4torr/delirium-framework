<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;

class ContainerServiceResolver implements ArgumentResolverInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return false;
        }

        return $this->container->has($type->getName());
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        /** @var \ReflectionNamedType $type */
        $type = $parameter->getType();
        return $this->container->get($type->getName());
    }
}
