<?php

declare(strict_types=1);

namespace Delirium\Http\Dispatcher;

use Delirium\Http\Contract\DispatcherInterface;
use Delirium\Http\Invoker\ControllerInvoker;
use Delirium\Http\Resolver\ArgumentResolverChain;
use Delirium\Http\Resolver\Response\ResponseResolverChain;
use Delirium\Http\Routing\Matcher\RegexRouteMatcher;
use Delirium\Http\Routing\Matcher\RouteMatcherInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegexDispatcher implements DispatcherInterface
{
    private RouteMatcherInterface $matcher;
    private ControllerInvoker $invoker;

    public function __construct(?RouteMatcherInterface $matcher = null, ?ControllerInvoker $invoker = null)
    {
        $this->matcher = $matcher ?? new RegexRouteMatcher();
        $this->invoker = $invoker ?? new ControllerInvoker();
    }

    public function addRoute(string $method, string $path, mixed $handler): void
    {
        $this->matcher->add($method, $path, $handler);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->invoker->setContainer($container);
    }

    public function dispatch(ServerRequestInterface $request): mixed
    {
        $match = $this->matcher->match($request);

        return $this->invoker->invoke($match->handler, $match->params, $request);
    }

    public function setArgumentResolverChain(ArgumentResolverChain $chain): void
    {
        $this->invoker->setArgumentResolverChain($chain);
    }

    public function setResponseResolverChain(ResponseResolverChain $chain): void
    {
        $this->invoker->setResponseResolverChain($chain);
    }
}
