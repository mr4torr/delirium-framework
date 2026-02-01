<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function addResolver(ArgumentResolverInterface $resolver): void
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

    private function resolveParameter(
        ServerRequestInterface|ResponseInterface $request,
        ReflectionParameter $parameter,
    ): mixed {
        foreach ($this->resolvers as $resolver) {
            if (!($resolver instanceof ArgumentResolverInterface && $resolver->supports($request, $parameter))) {
                continue;
            }

            return $resolver->resolve($request, $parameter);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(sprintf(
            'Could not resolve argument "$%s" for controller action.',
            $parameter->getName(),
        ));
    }
}
