# Quickstart: Core App Factory

## Minimal Example

### 1. Define a Module
```php
#[Module(
    controllers: [HelloController::class],
    providers: [SomeService::class],
    imports: [AuthModule::class] // Recursive Module Import
)]
class AppModule {}

#[Module(
    controllers: [AuthController::class]
)]
class AuthModule {}
```

### 2. Boot the Application
```php
use Delirium\Core\DeliriumFactory;

$app = DeliriumFactory::create(AppModule::class); // recurses into AuthModule
$app->listen(3000);
```
