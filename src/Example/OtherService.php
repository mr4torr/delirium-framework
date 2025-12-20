<?php

declare(strict_types=1);

namespace App\Example;

class OtherService
{
    public function __construct(
        private Other2Service $other2Service
    ) {}
    
    public function greet(string $name): string
    {
        return "Hello, {$name} other! " . $this->other2Service->greet($name);
    }
}
