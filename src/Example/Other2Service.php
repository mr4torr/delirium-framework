<?php

declare(strict_types=1);

namespace App\Example;

use Psr\Container\ContainerInterface;

class Other2Service
{
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function greet(string $name): string
    {
        return "Hello, {$name} other2!";
    }
}
