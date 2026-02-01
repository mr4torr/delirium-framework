<?php

declare(strict_types=1);

namespace Delirium\DI\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct(
        public ?string $serviceId = null,
    ) {}
}
