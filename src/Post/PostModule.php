<?php 

declare(strict_types=1);

namespace App\Post;

use Delirium\Core\Attribute\Module;

#[Module(
    controllers: [PostController::class]
)]
class PostModule
{
}