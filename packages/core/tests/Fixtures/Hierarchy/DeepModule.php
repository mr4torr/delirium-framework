<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Fixtures\Hierarchy;

use Delirium\Core\Attribute\Module;

#[Module(
    controllers: [DeepController::class]
)]
class DeepModule
{
}
