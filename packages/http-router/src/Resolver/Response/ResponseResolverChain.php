<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Response;

use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Contract\ResponseResolverInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResponseResolverChain
{
    /**
     * @var ResponseResolverInterface[]
     */
    private array $resolvers;

    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(mixed $data, ServerRequestInterface $request, array $attributes): ResponseInterface
    {

        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($data, $request, $attributes)) {
                return $resolver->resolve($data, $request, $attributes);
            }
        }

        throw new \RuntimeException('Unable to resolve response from controller return value.');
    }
}
