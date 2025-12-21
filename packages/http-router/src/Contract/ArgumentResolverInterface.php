<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;

interface ArgumentResolverInterface
{
    /**
     * Checks if this resolver can resolve the value for the given argument.
     *
     * @param ServerRequestInterface $request
     * @param ReflectionParameter $parameter
     * @return bool
     */
    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool;

    /**
     * Resolves the value for the given argument.
     *
     * @param ServerRequestInterface $request
     * @param ReflectionParameter $parameter
     * @return mixed
     */
    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed;
}
