<?php

declare(strict_types=1);

namespace Delirium\Http\Routing\Matcher;

use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

interface RouteMatcherInterface
{
    public function add(string $method, string $path, mixed $handler): void;

    /**
     * Match a request to a route.
     *
     * @throws RouteNotFoundException If no route matches the path.
     * @throws MethodNotAllowedException If path matches but method is not allowed.
     */
    public function match(ServerRequestInterface $request): RouteMatch;
}
