<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\DI\Attribute\Inject;

class PropertyInjectedController
{
    #[Inject]
    public GreetingService $greeter;

    public function greet(): string
    {
        return $this->greeter->greet('Property');
    }
}
