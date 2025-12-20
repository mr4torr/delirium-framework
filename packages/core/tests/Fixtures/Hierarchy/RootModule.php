<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Fixtures\Hierarchy;

use Delirium\Core\Attribute\Module;

#[Module(
    imports: [PublicModule::class],
    controllers: [RootController::class]
)]
class RootModule
{
}
