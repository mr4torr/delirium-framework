<?php

declare(strict_types=1);

namespace App\Examples\Controllers;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;
use Delirium\Http\Attribute\Post;
use Psr\Http\Message\ServerRequestInterface;

#[Controller('/api')]
class HelloController
{
    #[Get('/hello')]
    public function world(): string
    {
        return json_encode(['message' => 'Hello World from Delirium!']);
    }

    #[Get('/greet/{name}')]
    public function greet(string $name): string
    {
        return json_encode(['message' => "Hello, $name!"]);
    }

    #[Post('/echo')]
    public function echo(ServerRequestInterface $request): string
    {
        $body = (string) $request->getBody();
        return json_encode([
            'received' => json_decode($body, true),
            'method' => 'POST'
        ]);
    }
}
