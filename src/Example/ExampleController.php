<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;
use App\Example\GreetingService;
use Psr\Http\Message\ServerRequestInterface;

// Example Controller
#[Controller('/')]
class ExampleController
{
    public function __construct(
        private GreetingService $greeter
    ) {}

    #[Get]
    public function index(): string
    {
        return $this->greeter->greet('World é nois');
    }

    #[Get('/inject/{name}')]
    public function methodInjection(string $name, GreetingService $svc, ServerRequestInterface $req): string
    {
        return "Method Injection: " . $svc->greet($name) . " URL: " . $req->getUri()->getPath();
    }

    #[Get('/array/{name}')]
    public function contentArray(string $name, GreetingService $svc, ServerRequestInterface $req): array
    {
        return [
            'name' => $name,
            'greeting' => $svc->greet($name),
            'url' => $req->getUri()->getPath(),
            'params' => $req->getQueryParams(),
        ];
    }
}