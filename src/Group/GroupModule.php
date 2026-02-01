<?php

declare(strict_types=1);

namespace App\Group;

use Delirium\Core\Attribute\Module;

#[Module(controllers: [GroupController::class])]
class GroupModule {}
