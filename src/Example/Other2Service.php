<?php

declare(strict_types=1);

namespace App\Example;

class Other2Service
{
    public function __construct(
        protected \Psr\Container\ContainerInterface $container
    ) {
    }

    public function greet(string $name): string
    {
        return "Hello, {$name} other2!";
    }
}
