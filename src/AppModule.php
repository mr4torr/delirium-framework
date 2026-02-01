<?php

declare(strict_types=1);

namespace App;

use Delirium\Core\Attribute\Module;

// Example Root Module
#[Module(imports: [
    Example\ExampleModule::class,
    Post\PostModule::class,
])]
class AppModule {}
