<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Unit\Routing\Matcher;

use Delirium\Http\Exception\MethodNotAllowedException;
use Delirium\Http\Exception\RouteNotFoundException;
use Delirium\Http\Routing\Matcher\RegexRouteMatcher;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class RegexRouteMatcherTest extends TestCase
{
    private RegexRouteMatcher $matcher;
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->matcher = new RegexRouteMatcher();
        $this->factory = new Psr17Factory();
    }

    public function testMatchStaticRoute(): void
    {
        $this->matcher->add('GET', '/hello', 'HelloHandler');
        $request = $this->factory->createServerRequest('GET', '/hello');

        $match = $this->matcher->match($request);

        $this->assertEquals('HelloHandler', $match->handler);
        $this->assertEmpty($match->params);
    }

    public function testMatchDynamicRouteWithParams(): void
    {
        $this->matcher->add('GET', '/users/{id}', 'UserHandler');
        $request = $this->factory->createServerRequest('GET', '/users/123');

        $match = $this->matcher->match($request);

        $this->assertEquals('UserHandler', $match->handler);
        $this->assertEquals(['id' => '123'], $match->params);
    }

    public function testMatchDynamicRouteWithMultipleParams(): void
    {
        $this->matcher->add('GET', '/posts/{postId}/comments/{commentId}', 'CommentHandler');
        $request = $this->factory->createServerRequest('GET', '/posts/10/comments/20');

        $match = $this->matcher->match($request);

        $this->assertEquals('CommentHandler', $match->handler);
        $this->assertEquals(['postId' => '10', 'commentId' => '20'], $match->params);
    }

    public function testRouteNotFound(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $request = $this->factory->createServerRequest('GET', '/not-found');
        $this->matcher->match($request);
    }

    public function testMethodNotAllowed(): void
    {
        $this->matcher->add('POST', '/data', 'DataHandler');
        $request = $this->factory->createServerRequest('GET', '/data');

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage("Method GET not allowed. Allowed: POST");

        $this->matcher->match($request);
    }
}
