<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Fixtures\Hierarchy;

use Delirium\Core\Attribute\Module;

#[Module(
    imports: [DeepModule::class],
    controllers: [PublicController::class]
)]
class PublicModule
{
}
