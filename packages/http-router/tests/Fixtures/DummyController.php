<?php

declare(strict_types=1);

namespace Delirium\Http\Tests\Fixtures;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;
use Delirium\Http\Attribute\Post;

#[Controller('/dummy')]
class DummyController
{
    #[Get('/hello')]
    public function hello(): string
    {
        return 'world';
    }

    #[Post('/create')]
    public function create(): void
    {
    }
}
