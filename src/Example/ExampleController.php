<?php

declare(strict_types=1);

namespace App\Example;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;

// Example Controller
#[Controller('/')]
class ExampleController
{
    #[Get('/')]
    public function index(): string
    {
        return 'Hello from Delirium Framework!';
    }
}