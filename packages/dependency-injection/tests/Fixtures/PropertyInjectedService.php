<?php

namespace Delirium\DI\Tests\Fixtures;

use Delirium\DI\Attribute\Inject;

class PropertyInjectedService
{
    #[Inject]
    public SimpleService $service;

    public function greet(): string
    {
        return $this->service->sayHello() . ' Property';
    }
}
