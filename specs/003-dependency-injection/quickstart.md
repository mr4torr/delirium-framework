# Quickstart: Dependency Injection

## 1. Defining a Service

Create a service class. It is automatically registered if it is imported by a Module (or autoregistered by namespace scanning if configured).

```php
namespace App\Service;

class UserService
{
    public function find(int $id): string
    {
        return "User $id";
    }
}
```

## 2. Constructor Injection

Type-hint the service in your Controller's constructor.

```php
namespace App\Controller;

use App\Service\UserService;

class UserController
{
    public function __construct(
        private UserService $users
    ) {}

    #[Get('/users/{id}')]
    public function show(int $id): Response
    {
        return new Response($this->users->find($id));
    }
}
```

## 3. Function Injection

Type-hint the service in your Controller's function.

```php
namespace App\Controller;

use App\Service\UserService;

class UserController
{
    #[Get('/users/{id}')]
    public function show(int $id, UserService $users): Response
    {
        return new Response($users->find($id));
    }
}
```

## 4. Property Injection

Use the `#[Inject]` attribute on properties.

```php
namespace App\Controller;

use Delirium\DI\Attribute\Inject;
use App\Service\LoggerService;

class BaseController
{
    #[Inject]
    protected LoggerService $logger;
}
```

## 4. Module Registration

Ensure your controllers are part of a Module. Note that you **do not** need to explicitly register services in `providers` if they are type-hinted in your controllers; the system will find them automatically.

```php
namespace App;

use Delirium\Core\Attribute\Module;
use App\Controller\UserController;

#[Module(
    controllers: [UserController::class],
    // providers: [UserService::class] <-- Optional! Auto-discovered.
)]
class AppModule {}
```
