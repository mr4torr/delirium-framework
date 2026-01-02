<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Delirium\Http\Contract\ResponseResolverInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionParameter;
use RuntimeException;

class ArgumentResolverChain
{
    /**
     * @var ArgumentResolverInterface[]|ResponseResolverInterface[]
     */
    private array $resolvers = [];

    public function __construct(iterable $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    public function addResolver(ArgumentResolverInterface|ResponseResolverInterface $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    public function resolveArguments(ServerRequestInterface $request, array $parameters): array
    {
        $arguments = [];

        foreach ($parameters as $parameter) {
            $arguments[] = $this->resolveParameter($request, $parameter);
        }

        return $arguments;
    }

    public function resolveResults(ResponseInterface $response, mixed $results): mixed
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof ResponseResolverInterface && $resolver->supports($response, $results)) {
                return $resolver->resolve($response, $results);
            }
        }

        throw new RuntimeException('Could not resolve result for controller action.');
    }

    private function resolveParameter(ServerRequestInterface|ResponseInterface $request, ReflectionParameter $parameter): mixed
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof ArgumentResolverInterface && $resolver->supports($request, $parameter)) {
                return $resolver->resolve($request, $parameter);
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(sprintf(
            'Could not resolve argument "$%s" for controller action.',
            $parameter->getName()
        ));
    }
}
