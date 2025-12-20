<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;
use App\Example\GreetingService;
use Psr\Container\ContainerInterface;

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
    public function methodInjection(string $name, GreetingService $svc): string
    {
        return "Method Injection: " . $svc->greet($name);
    }
}