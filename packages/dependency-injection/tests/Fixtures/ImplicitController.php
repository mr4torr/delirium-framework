<?php

namespace Delirium\DI\Tests\Fixtures;

class ImplicitController
{
    public function action(SimpleService $service): void {}
}
