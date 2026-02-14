<?php

declare(strict_types=1);

namespace App\Example;

use App\Group\GroupModule;
use Delirium\Core\Attribute\Module;
use Delirium\Core\Attribute\ModuleImport;

// Example Root Module
#[Module(
    controllers: [
        ExampleController::class,
        PropertyInjectedController::class,
    ],
    providers: [
        // GreetingService::class
    ],
    imports: [
        GroupModule::class,
        new ModuleImport(GroupModule::class, 'subgroup'),
    ],
)]
class ExampleModule {}
