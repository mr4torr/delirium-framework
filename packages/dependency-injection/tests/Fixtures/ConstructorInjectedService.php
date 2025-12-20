<?php

namespace Delirium\DI\Tests\Fixtures;

class ConstructorInjectedService
{
    public function __construct(
        public SimpleService $service
    ) {}
}
