<?php

declare(strict_types=1);

namespace App\Post;

use Delirium\Http\Attribute\Controller;
use Delirium\Http\Attribute\MapRequestPayload;
use Delirium\Http\Attribute\Post;
use Nyholm\Psr7\Response;



use Symfony\Component\Validator\Constraints as Assert;

class PostDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $name;

    // #[Assert\Email]
    public string $email;

    #[Assert\Type('integer')]
    public int $age;
}



#[Controller('/post')]
class PostController
{
    // #[ResponseJson]
    #[Post('/{name}')]
    public function index(
        #[MapRequestPayload]
        PostDto $dto,
        string $name
    ): array {
        return [
            'name' => $name,
            'title' => $dto->name,
            'email' => $dto->email,
            'age' => $dto->age,
        ];
    }
}