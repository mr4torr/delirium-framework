<?php

declare(strict_types=1);

namespace App\Group;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\Get;

#[Controller('/group')]
class GroupController
{
    #[Get('/')]
    public function index(): string
    {
        return 'Hello is group!';
    }
}
