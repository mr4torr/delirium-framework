<?php 

declare(strict_types=1);

namespace App\Example;

use App\Group\GroupModule;
use Delirium\Core\Attribute\Module;

// Example Root Module
#[Module(
    controllers: [
        ExampleController::class,
        PropertyInjectedController::class,
        PsrInjectionController::class
    ],
    providers: [
        // GreetingService::class
    ],
    imports: [GroupModule::class]
)]
class ExampleModule
{
}