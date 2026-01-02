<?php

declare(strict_types=1);

namespace Delirium\Http\Contract;

use Psr\Http\Message\ResponseInterface;

interface ResponseResolverInterface
{
    /**
     * Checks if this resolver can resolve the value for the given argument.
     *
     * @param ResponseInterface $response
     * @param mixed $results
     * @return bool
     */
    public function supports(ResponseInterface $response, mixed $results): bool;

    /**
     * Resolves the value for the given argument.
     *
     * @param ResponseInterface $response
     * @param mixed $results
     * @return mixed
     */
    public function resolve(ResponseInterface $response, mixed $results): mixed;
}
