# Quickstart: Map Request Payload & Validation

Automatically map and validate JSON request bodies into typed objects in your Controllers.

## 1. Create a DTO

Define a class to represent your request data using public properties or constructor promotion. Add validation attributes from `Delirium\Validation\Attribute`.

```php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $name;

    #[Assert\Email]
    public string $email;

    #[Assert\Type('integer')]
    public int $age;
}
```

## 2. Use in Controller

Add the DTO as an argument to your controller method and mark it with `#[MapRequestPayload]`.

```php
namespace App\Controller;

use Delirium\Http\Attribute\MapRequestPayload;
use App\Dto\CreateUserDto;

class UserController
{
    public function create(#[MapRequestPayload] CreateUserDto $dto): array
    {
        // $dto is already instantiated, hydrated, and validated!
        return [
            'status' => 'created',
            'user' => $dto->name
        ];
    }
}
```

## 3. Error Handling

If validation fails, the framework automatically returns a `422 Unprocessable Entity` response with the error details. If the JSON is malformed, it returns a `400 Bad Request`.
