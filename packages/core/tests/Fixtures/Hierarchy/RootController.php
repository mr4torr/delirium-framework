<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Fixtures\Hierarchy;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;

#[Controller('/')]
class RootController
{
    #[Get('/')]
    public function index(): string
    {
        return 'root';
    }
}
