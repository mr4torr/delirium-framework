<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\DI\Attribute\Inject;

class GreetingService
{
    #[Inject]
    public OtherService $other;

    public function greet(string $name): string
    {
        return "Hello, " . $this->other->greet($name) . '!';
    }
}
