<?php

declare(strict_types=1);

namespace Delirium\Http\Resolver\Request;

use Delirium\Http\Contract\ArgumentResolverInterface;
use Delirium\Http\Contract\ResponseInterface;
use Delirium\Http\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use ReflectionParameter;

class ResponseResolver implements ArgumentResolverInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {}

    public function supports(ServerRequestInterface $request, ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();
        if (!$type || !$type instanceof \ReflectionNamedType) {
            return false;
        }

        $name = $type->getName();
        return is_a($name, ResponseInterface::class, true) ||
               is_a($name, \Psr\Http\Message\ResponseInterface::class, true);
    }

    public function resolve(ServerRequestInterface $request, ReflectionParameter $parameter): mixed
    {
        $status = 200;
        $type = \Delirium\Http\Enum\ResponseTypeEnum::JSON;

        $func = $parameter->getDeclaringFunction();
        // Check for Delirium Route Attributes.
        $attributes = $func->getAttributes(\Delirium\Http\Attribute\RouteAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (!empty($attributes)) {
             $routeAttr = $attributes[0]->newInstance();
             if (isset($routeAttr->status)) {
                 $status = $routeAttr->status;
             }
             if (isset($routeAttr->type)) {
                 $type = $routeAttr->type;
             }
        }

        $psrResponse = $this->responseFactory->createResponse($status);
        $response = new Response($psrResponse, $this->streamFactory);

        // Apply default Content-Type based on attribute type
        if ($type === \Delirium\Http\Enum\ResponseTypeEnum::JSON) {
             $response = $response->withHeader('Content-Type', 'application/json');
        } elseif ($type === \Delirium\Http\Enum\ResponseTypeEnum::HTML) {
             $response = $response->withHeader('Content-Type', 'text/html');
        } elseif ($type === \Delirium\Http\Enum\ResponseTypeEnum::XML) {
             $response = $response->withHeader('Content-Type', 'application/xml');
        }
        // Stream and Raw might not have specific default CT here or handled elsewhere.

        return $response;
    }
}
